<?php

namespace App\Http\Controllers;

use App\Models\DataQueryRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\Approval\CommonService;


class DataQueryRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $user = user();

            $data_query_requests = DataQueryRequest::query()
                ->where(function ($query) use ($user) {

                    if (role_can('data.query.request', $user)) {
                        $query->where('requested_by', $user->id)
                            ->orWhere('requested_to', $user->id);
                    } else {
                        $query->where('requested_to', $user->id);
                    }

                })
                ->where('query_for_model', $request->query_for_model)
                ->where('query_for_model_id', $request->query_for_model_id)
                ->with(['requestedBy','requestedTo'])
                ->orderBy('created_at', 'desc')
                ->get();

            // ✅ GROUP SAME AS YOUR EXISTING LOGIC
            $groupedQueries = $data_query_requests->groupBy(function ($item) {
                return md5(
                    $item->requested_by .
                    $item->query .
                    $item->query_for_model .
                    $item->query_for_model_id .
                    $item->created_at->format('Y-m-d H:i')
                );
            });

            // ✅ RETURN PARTIAL WITH GROUPED DATA
            return view('data-query-request.partials.list', compact('groupedQueries'))->render();
        }

        return view('data-query-request.query');
    }

    function store(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array', // ✅ NEW
            'employee_ids.*' => 'exists:users,id', // ✅ NEW
            'query' => 'required|max:255',
        ]);

        $user = $request->auth_user ?? user();
        abort_if(!role_can('data.query.request', $user), 403);

        $employeeIds = array_unique($request->employee_ids); // ✅ NEW

        $createdQueries = [];

        foreach ($employeeIds as $empId) {

            $dataQueryRequest = DataQueryRequest::create([
                'requested_by' => $user->id,
                'requested_to' => $empId,
                'query' => $request->input('query'),
                'query_for_model' => $request->input('query_for_model'),
                'query_for_model_id' => $request->input('query_for_model_id'),
            ]);

            if ($dataQueryRequest->id) {
                app(CommonService::class)->onQuery($dataQueryRequest, user(), []); // same as before
                $createdQueries[] = $dataQueryRequest; // ✅ NEW
            }
        }

        return response()->json([
            'message' => 'Query sent successfully.', // 🔁 UPDATED
            'data' => $createdQueries // 🔁 UPDATED
        ]);
    }

    function answer(Request $request)
    {
        $request->validate([
            'data_query_request_id' => 'required|exists:data_query_requests,id',
            'answer' => 'required|max:2000',
        ]);
        $user = $request->auth_user ?? user();
        $dataQueryRequest = DataQueryRequest::find($request->input('data_query_request_id'));
        $dataQueryRequest->query_response_description = $request->input('answer');
        $dataQueryRequest->status = 'answered';
        $dataQueryRequest->save();

        if ($dataQueryRequest->id) {

            $id = $dataQueryRequest->id;
            $req = DataQueryRequest::find($id);
            // dd($req->requestedBy->name);

            app(CommonService::class)->onQueryAnswer($req, user(), [
            ]);
        }

        return response()->json([
            'message' => 'Answer saved successfully.',
            'data_query_request' => $dataQueryRequest
        ]);
    }
}
