<?php

namespace App\Http\Controllers;

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessStep;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalProcessController extends Controller
{
    function can()
    {
        return abort_if(!role_can('approval-processes'), 403, 'Unauthorized for this action');
    }

    function checkIfNotUniqueUserByType($type, $userId, $ignoreId = null)
    {
        $processes = ApprovalProcess::query()
            ->when($ignoreId, function ($query) use ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            })
            ->where('type', $type)
            ->where('for_users', 'like', '%"' . $userId . '"%')
            ->first();
        return $processes;
    }

    public function index()
    {
        $this->can();
        $processes = ApprovalProcess::with('steps.approver')
            ->orderBy('created_at', 'desc')
            ->get();
        $users = User::active_employees()->get();
        $types = ApprovalProcess::$types;
        return view('approval-processes.index', compact('processes', 'users', 'types'));
    }

    public function create()
    {
        $this->can();
        $users = User::active_employees()->get(); // Fetch all employees to assign as approvers
        return view('approval-processes.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->can();
        $request->validate([
            'name' => 'required|string|unique:approval_processes,name',
            'for_users' => 'nullable|array',
            'for_users.*' => 'exists:users,id',
            'type' => 'required|array', // |in:' . implode(',', ApprovalProcess::$types),
        ]);
        $id = \Illuminate\Support\Str::uuid();
        foreach ($request->type as $type) {
            foreach ($request->for_users ?? [] as $userId) {
                $check = $this->checkIfNotUniqueUserByType($type, $userId);
                if ($check) {
                    return redirect()->back()->with('error', 'User [' . $userId . '] is already assigned to this type of approval process.');
                }
            }
            ApprovalProcess::create([
                'name' => $request->name,
                'for_users' => $request->for_users,
                'type' => $type,
                'group' => $id,
            ]);
        }
        return redirect()->route('approval-processes.index')
            ->with('success', 'Approval process created.');
    }

    public function edit(ApprovalProcess $approvalProcess)
    {
        $this->can();
        $users = User::active_employees()->get();
        $types = ApprovalProcess::$types;
        $groupedTypes = $approvalProcess->group ? ApprovalProcess::query()->where('group', $approvalProcess->group)
            ->get()->pluck('type')->toArray() : [$approvalProcess->type];
        return view('approval-processes.edit', compact('approvalProcess', 'users', 'types', 'groupedTypes'));
    }

    public function update(Request $request, ApprovalProcess $approvalProcess)
    {
        $this->can();

        $validated = $request->validate([
            'name' => 'required|string',
            'type' => 'required|array|min:1',
            'type.*' => 'required|string|in:' . implode(',', ApprovalProcess::$types),
            'for_users' => 'nullable|array',
            'for_users.*' => 'exists:users,id',
        ]);

        try {
            DB::transaction(function () use ($approvalProcess, $validated) {
                $processes = $approvalProcess->group
                    ? ApprovalProcess::where('group', $approvalProcess->group)
                        ->lockForUpdate()->get()
                    : ApprovalProcess::whereKey($approvalProcess->id)
                        ->lockForUpdate()->get();

                $existingTypes = $processes->pluck('type')->sort()->values()->all();
                $selectedTypes = collect($validated['type'])
                    ->unique()->sort()->values()->all();

                if ($existingTypes !== $selectedTypes) {
                    throw new \RuntimeException(
                        'Approval types cannot be changed during an update.'
                    );
                }

                foreach ($processes as $process) {
                    $process->update([
                        'name' => $validated['name'],
                        'for_users' => $validated['for_users'] ?? null,
                    ]);
                }
            });

            return redirect()
                ->route('approval-processes.index')
                ->with('success', 'Approval process updated. Existing steps preserved.');

        } catch (\Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function destroy(ApprovalProcess $approvalProcess)
    {
        $this->can();

        DB::beginTransaction();

        try {

            if ($approvalProcess->group) {

                $processes = ApprovalProcess::where('group', $approvalProcess->group)->get();

                foreach ($processes as $process) {
                    // delete related steps first
                    $process->steps()->delete();
                    // delete process
                    $process->delete();
                }

            } else {
                // delete steps
                $approvalProcess->steps()->delete();
                // delete process
                $approvalProcess->delete();
            }

            DB::commit();

            return redirect()
                ->route('approval-processes.index')
                ->with('success', 'Approval process and steps deleted successfully.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Delete failed!')
                ->withErrors($e->getMessage());
        }
    }


    public function addStep(Request $request, ApprovalProcess $process)
    {
        $this->can();
        $request->validate([
            'approver_id' => 'required|exists:users,id',
            'step_order' => 'required|integer|min:1',
            'is_publisher' => 'nullable',
        ]);

        $processes = [$process];
        if ($process->group) {
            $processes = ApprovalProcess::query()
                ->where('group', $process->group)
                ->get();
        }
        $isPublisher = $request->is_publisher == "on";
        foreach ($processes as $process) {
            $process->steps()->create([
                'approver_id' => $request->approver_id,
                'step_order' => $request->step_order,
                'is_publisher' => $isPublisher,
            ]);
        }
        return redirect()->back()->with('success', 'Step added to the approval process.');
    }

    public function removeStep(ApprovalProcess $process, ApprovalProcessStep $step)
    {
        $this->can();

        if ($step->approval_process_id != $process->id) {
            abort(404, 'Approval step does not belong to this process.');
        }

        try {
            DB::transaction(function () use ($process, $step) {
                $processes = $process->group
                    ? ApprovalProcess::where('group', $process->group)->get()
                    : collect([$process]);

                foreach ($processes as $groupProcess) {
                    $groupProcess->steps()
                        ->where('step_order', $step->step_order)
                        ->where('approver_id', $step->approver_id)
                        ->delete();
                }
            });

            return redirect()->back()
                ->with('success', 'Approver removed. Other approval steps preserved.');

        } catch (\Throwable $e) {
            return redirect()->back()
                ->with('error', 'Unable to remove approver.');
        }
    }
}
