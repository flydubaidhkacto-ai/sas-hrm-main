<?php

namespace App\Http\Controllers;

use App\Models\Appraisal;
use App\Models\AppraisalCriterion;
use App\Support\AppraisalAccess;
use App\Models\User;
use App\Support\Scoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppraisalController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $q = Appraisal::with(['employee.department', 'employee.designation'])->latest();

        if (!AppraisalAccess::seesAll($u)) {
            $q->where(function ($w) use ($u) {
                $w->where('employee_id', $u->id)->orWhere('supervisor_id', $u->id);
                if (AppraisalAccess::is($u, 'hod')) {
                    $w->orWhereHas('employee', fn ($e) => $e->where('department_id', $u->department_id));
                }
            });
        }

        return view('appraisals.index', [
            'appraisals' => $q->paginate(15),
            'user' => $u,
        ]);
    }

    public function create(Request $request)
    {
        abort_unless(AppraisalAccess::isHr($request->user()), 403);

        return view('appraisals.create', [
            'employees' => User::active_employees()->whereNotNull('appraisal_supervisor_id')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        abort_unless(AppraisalAccess::isHr($request->user()), 403);

        $data = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'period' => 'required|string|max:100',
        ]);

        $employee = User::findOrFail($data['employee_id']);
        if (!$employee->appraisal_supervisor_id) {
            return back()->withErrors(['employee_id' => 'This employee has no appraisal supervisor assigned (see Appraisal Setup).'])->withInput();
        }

        $appraisal = DB::transaction(function () use ($data, $employee, $request) {
            $a = Appraisal::create([
                'employee_id' => $employee->id,
                'supervisor_id' => $employee->appraisal_supervisor_id,
                'created_by' => $request->user()->id,
                'period' => $data['period'],
            ]);
            foreach (AppraisalCriterion::orderBy('code')->get() as $c) {
                $a->ratings()->create(['criterion_id' => $c->id]);
            }
            return $a;
        });

        return redirect()->route('appraisals.show', $appraisal)->with('status', 'Appraisal created.');
    }

    public function show(Request $request, Appraisal $appraisal)
    {
        $u = $request->user();
        abort_unless($appraisal->visibleTo($u), 403);

        $appraisal->load(['employee.department', 'employee.designation', 'supervisor', 'ratings.criterion', 'jobItems', 'reviews.reviewer']);

        return view('appraisals.show', [
            'appraisal' => $appraisal,
            'canAct' => $appraisal->canBeActedBy($u),
            'ratings' => $appraisal->ratings->sortBy('criterion.code'),
        ]);
    }

    public function print(Request $request, Appraisal $appraisal)
    {
        abort_unless($appraisal->visibleTo($request->user()), 403);
        $appraisal->load(['employee.department', 'employee.designation', 'supervisor', 'ratings.criterion', 'jobItems', 'reviews.reviewer']);

        return view('appraisals.print', [
            'appraisal' => $appraisal,
            'ratings' => $appraisal->ratings->sortBy('criterion.code'),
        ]);
    }

    /** Sections 1-3 + supervisor's result block */
    public function saveSupervisor(Request $request, Appraisal $appraisal)
    {
        abort_unless($appraisal->status === 'pending_supervisor' && $appraisal->canBeActedBy($request->user()), 403);

        $data = $request->validate([
            'ratings' => 'required|array',
            'ratings.*.level' => 'nullable|in:low,mid,high',
            'ratings.*.marks' => 'required|integer|min:1|max:10',
            'ratings.*.comment' => 'nullable|string|max:2000',
            'jobs' => 'nullable|array|max:10',
            'jobs.*.description' => 'nullable|string|max:500',
            'jobs.*.is_satisfactory' => 'nullable|in:0,1',
            'jobs.*.standard' => 'nullable|in:BS,MS,ES',
            'review.marks' => 'nullable|numeric|min:0|max:100',
            'review.recommendation' => 'required|in:' . implode(',', Scoring::RECOMMENDATIONS),
            'review.increment_steps' => 'required|integer|min:1|max:4',
            'review.comment' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($data, $appraisal, $request) {
            foreach ($data['ratings'] as $id => $r) {
                $appraisal->ratings()->whereKey($id)->update([
                    'level' => $r['level'] ?? null,
                    'marks' => $r['marks'],
                    'comment' => $r['comment'] ?? null,
                ]);
            }

            $appraisal->jobItems()->delete();
            foreach ($data['jobs'] ?? [] as $j) {
                if (blank($j['description'] ?? null)) {
                    continue;
                }
                $appraisal->jobItems()->create([
                    'description' => $j['description'],
                    'is_satisfactory' => isset($j['is_satisfactory']) && $j['is_satisfactory'] !== '' ? (bool) $j['is_satisfactory'] : null,
                    'standard' => $j['standard'] ?? null,
                ]);
            }

            $appraisal->total_percent = $appraisal->computeTotal();
            $appraisal->save();

            $this->storeReview($appraisal, 'supervisor', $data['review'], $request->user());
            $appraisal->advance();
        });

        return redirect()->route('appraisals.show', $appraisal)->with('status', 'Supervisor evaluation submitted.');
    }

    /** Section 4 - Employee comment */
    public function saveEmployee(Request $request, Appraisal $appraisal)
    {
        abort_unless($appraisal->status === 'pending_employee' && $appraisal->canBeActedBy($request->user()), 403);

        $data = $request->validate(['employee_comment' => 'nullable|string|max:5000']);

        $appraisal->update([
            'employee_comment' => $data['employee_comment'] ?? null,
            'employee_signed_at' => now(),
        ]);
        $appraisal->advance();

        return redirect()->route('appraisals.show', $appraisal)->with('status', 'Your comment has been saved.');
    }

    /** HOD / HR / MD review */
    public function review(Request $request, Appraisal $appraisal, string $stage)
    {
        abort_unless(in_array($stage, ['hod', 'hr', 'md'], true), 404);
        abort_unless($appraisal->status === "pending_$stage" && $appraisal->canBeActedBy($request->user()), 403);

        $data = $request->validate([
            'marks' => 'required|numeric|min:0|max:100',
            'recommendation' => 'required|in:' . implode(',', Scoring::RECOMMENDATIONS),
            'increment_steps' => 'required|integer|min:1|max:4',
            'comment' => 'nullable|string|max:2000',
        ]);

        DB::transaction(function () use ($appraisal, $stage, $data, $request) {
            $this->storeReview($appraisal, $stage, $data, $request->user());
            if ($stage === 'md') {
                $appraisal->final_marks = $data['marks'];
            }
            $appraisal->advance();
        });

        return redirect()->route('appraisals.show', $appraisal)->with('status', 'Review submitted.');
    }

    /** Assign appraisal roles + supervisors (HR / super admin only) */
    public function setup(Request $request)
    {
        abort_unless(AppraisalAccess::isHr($request->user()), 403);

        $users = User::active_employees()->with('department')->orderBy('name')->get();
        return view('appraisals.setup', [
            'users' => $users,
            'roles' => AppraisalAccess::ROLES,
        ]);
    }

    public function setupSave(Request $request)
    {
        abort_unless(AppraisalAccess::isHr($request->user()), 403);

        $data = $request->validate([
            'users' => 'required|array',
            'users.*.appraisal_role' => 'nullable|in:' . implode(',', array_keys(AppraisalAccess::ROLES)),
            'users.*.appraisal_supervisor_id' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['users'] as $id => $row) {
                User::whereKey($id)->update([
                    'appraisal_role' => $row['appraisal_role'] ?: null,
                    'appraisal_supervisor_id' => $row['appraisal_supervisor_id'] ?: null,
                ]);
            }
        });

        return redirect()->route('appraisals.setup')->with('status', 'Appraisal setup saved.');
    }

    private function storeReview(Appraisal $appraisal, string $stage, array $d, User $user): void
    {
        $marks = (float) ($d['marks'] ?? $appraisal->total_percent ?? 0);

        $appraisal->reviews()->updateOrCreate(
            ['stage' => $stage],
            [
                'reviewer_id' => $user->id,
                'marks' => $marks,
                'result_band' => Scoring::band($marks)['label'],
                'recommendation' => $d['recommendation'],
                'increment_steps' => $d['increment_steps'],
                'comment' => $d['comment'] ?? null,
                'signed_at' => now(),
            ]
        );
    }
}
