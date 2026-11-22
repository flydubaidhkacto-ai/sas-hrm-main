<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use App\Models\NoticeView;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticeController extends Controller
{
    function notices(Request $request)
    {
        if ($request->id) {
            return NoticeView::create([
                'employee_id' => $request->auth_user->id,
                'notice_id' => $request->id
            ]);
        }
        $DATA = Notice::whereDoesntHave('userView', function ($query) use ($request) {
            $query->where('employee_id', $request->auth_user->id);
        })
            // ->where('created_by', '!=', user()->id)
            ->with('attachments')
            ->limit(($request->limit ?? 3) < 10 ? ($request->limit ?? 3) : 3)
            ->get();
        return ApiResponseClass::sendResponse($DATA, 'Notices Fetched Successfully');
    }
    function index(Request $request)
    {
        $notices = Notice::query()
            ->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', $request->end_date . ' 23:59:59'
            ])
            ->with('attachments')
            ->get()
            ->map(function ($d) {
                $d->user_view_count = $d->userView()->count() . '/' . User::active_employees()->count();
                return $d;
            });
        return ApiResponseClass::sendResponse($notices, 'Notfices Fetched Successfully');
    }
    function store(Request $request)
    {
        abort_if(!role_can('notice_admin', $request->auth_user), 403);
        $notice = Notice::create([
            'title' => $request->title,
            'description' => $request->description,
            'created_by' => $request->auth_user->id
        ]);
        $notice->userView()->create([
            'employee_id' => $request->auth_user->id
        ]);
        if ($request->attachments) {
            foreach ($request->attachments as $index => $document) {
                if (($document['file'] ?? null) && ($document['type'] ?? null)) {
                    $notice->attachments()->create([
                        'path' => Storage::put('notices/attachments', $document['file']),
                        'type' => $document['type'],
                    ]);
                }
            }
        }
        return ApiResponseClass::sendResponse($notice, 'Notice Created Successfully');
    }
    function show($id)
    {
        $notice = Notice::findOrFail($id)
            ->load('attachments');
        $notice->user_view_count = $notice->userView()->count() . '/' . User::active_employees()->count();
        return ApiResponseClass::sendResponse($notice, 'Notice Fetched Successfully');
    }
    function delete($id, Request $request)
    {
        abort_if(!role_can('notice_admin', $request->auth_user), 403);
        $notice = Notice::findOrFail($id);
        $notice->delete();
        return ApiResponseClass::sendResponse([], 'Notice Deleted Successfully');
    }
    function update($id, Request $request)
    {
        abort_if(!role_can('notice_admin', $request->auth_user), 403);
        $notice = Notice::findOrFail($id);
        $notice->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);
        return ApiResponseClass::sendResponse($notice, 'Notice Updated Successfully');
    }
}
