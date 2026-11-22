<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\Models\NoticeAttachment;
use App\Models\NoticeView;
use App\Models\User;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    public function index()
    {
        return view('notice.notice', [
            'notices' => Notice::latest()
                ->withCount('userView')
                ->with('attachments')
                ->paginate(),
            'total_users' => User::active_employees()->count(),
        ]);
    }
    function create()
    {
        return view('notice.create');
    }
    public function store(Request $request)
    {
        abort_if(!role_can('notice_admin'), 403);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable',
            'document.*.file' => 'nullable|file|max:10240',
            'document.*.type' => 'nullable|string|max:255',
        ]);

        $notice = new Notice();
        $notice->title = $request->title;
        $notice->description = $request->description ?? '';
        $notice->created_by = user()->id;
        $notice->save();
        if ($request->document) {
            foreach ($request->document as $index => $document) {
                if (($document['file'] ?? null) && ($document['type'] ?? null)) {
                    $notice->attachments()->create([
                        'path' => Storage::put('notices/attachments', $document['file']),
                        'type' => $document['type'],
                    ]);
                }
            }
        }
        // dd($notice->attachments);
        Toastr::success('Notice created successfully!', 'success');
        return redirect()->route('notice.index');
    }

    public function edit($id)
    {
        $notice = Notice::find($id);
        return view('notice.edit', [
            'notice' => $notice
        ]);
    }

    public function update(Request $request)
    {
        abort_if(!role_can('notice_admin'), 403);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable',
            'document.*.file' => 'nullable|file|max:10240',
            'document.*.type' => 'nullable|string|max:255',
        ]);
        $notice = Notice::find($request->d_id);
        $notice->title = $request->title;
        $notice->description = $request->description ?? '';
        $notice->save();
        NoticeView::query()->where('notice_id', $notice->id)->delete();

        if ($request->document_delete) {
            foreach ($request->document_delete as $document_id) {
                $doc = NoticeAttachment::find($document_id);
                if ($doc) {
                    $doc->delete();
                }
            }
        }
        if ($request->document) {
            foreach ($request->document as $index => $document) {
                if (($document['file'] ?? null) && ($document['type'] ?? null)) {
                    $notice->attachments()->create([
                        'path' => Storage::put('notices/attachments', $document['file']),
                        'type' => $document['type'],
                    ]);
                }
            }
        }

        Toastr::success('Notice update successfully!', 'success');
        return redirect()->back();
    }
    public function delete(Request $request)
    {
        abort_if(!role_can('notice_admin'), 403);
        $notice = Notice::find($request->id);
        $notice->delete();
        Toastr::success('Notice delete successfully!', 'success');
        return redirect()->back();
    }

    public function markAsViewed(Request $request)
    {
        $request->validate([
            'notice_id' => 'required|exists:notices,id',
        ]);

        NoticeView::firstOrCreate([
            'employee_id' => user()->id,
            'notice_id' => $request->notice_id,
        ]);

        // return response()->json(['success' => true]);
    }
    public function view(Request $request)
    {
        return view('notice.view-all', [
            'notices' => Notice::latest()
                ->where('created_by', '!=', user()->id)
                ->when($request->id, fn($q) => $q->where('id', $request->id))
                ->paginate(),
        ]);
    }
    function show($id)
    {
        return view('notice.show', [
            'notice' => Notice::find($id)->load('attachments'),
        ]);
    }
    function viewers($id)
    {
        $notice = Notice::find($id);
        if (!$notice) {
            return response()->json(['error' => 'Notice not found'], 404);
        }
        $viewers = $notice->userView()
            ->with('user')
            ->get()
            ->map(function ($view) {
                return [
                    'employee_id' => $view->user->employee_id ?: 'N/A',
                    'name' => $view->user->name,
                    'viewed_at' => $view->created_at->format('M d, Y h:i A'),
                ];
            });
        return response()->json([
            'notice_id' => $notice->id,
            'notice_title' => $notice->title,
            'total_viewers' => $viewers->count(),
            'viewers' => $viewers,
        ]);
    }
}
