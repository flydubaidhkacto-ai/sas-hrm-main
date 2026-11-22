<?php

namespace App\Http\Controllers\Api\MobileApi;

use App\Classes\ApiResponseClass;
use App\Http\Controllers\Controller;
use App\Http\Resources\RosterResource;
use App\Models\Roster;
use App\Models\RosterItem;
use App\Services\RosterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RosterController extends Controller
{

    public function __construct(protected RosterService $rosterService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $rosters = $this->rosterService->index();
        return ApiResponseClass::sendResponse(RosterResource::collection($rosters), 'Roster fetched successfully', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $formData = $request->validate([
            'name' => 'required|string|max:255',
            'data' => 'required|array',
            'data.*.employees' => 'required|array',
            'data.*.shifts' => 'required|array',
        ]);
        DB::beginTransaction();
        try {
            $roster = $this->rosterService->store($formData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return ApiResponseClass::sendResponse(new RosterResource($roster), 'Roster created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $roster = Roster::findOrFail($id)->load('items');
        return [
            'roster' => $roster,
            'formatted' => $roster->formatted(),
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function my_roster(Request $request)
    {
        $formData = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);
        $user = $request->auth_user;
        $employee = $request->employee_id;
        $startDate = Carbon::parse($formData['start_date']);
        $endDate = Carbon::parse($formData['end_date']);
        $dates = generateDateRange($startDate, $endDate);
        $items = [];
        $roster = null;
        foreach ($dates as $date) {
            $item = RosterItem::where('employee_id', $employee)
                ->whereHas('roster', function ($query) {
                    $query->where('is_active', true);
                })
                ->where('date', $date)
                ->with(['shift', 'roster'])
                ->first();
            if ($item && $item->roster) {
                $roster = $item->roster;
            }
            $data = $item ?: [
                'date' => $date,
                'shift' => null,
                'not_set' => true,
            ];
            if ($roster && $item) {
                $data['no_shift_duty'] = $roster->no_shift_duty[$item->group][$date] ?? null;
            }
            $items[] = $data;
        }
        return ApiResponseClass::sendResponse([
            'rosters' => $items,
            'roster' => $roster,
        ], 'Roster fetched successfully', 200);
    }
}
