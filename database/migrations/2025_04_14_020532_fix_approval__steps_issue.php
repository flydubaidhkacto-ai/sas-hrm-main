<?php

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessStep;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $groups = ApprovalProcess::query()->whereNotNull('group')->pluck('group')->unique();
        foreach($groups as $group)
        {
            $rawSteps = []; // [step_order => approver_id, ...]
            $steps = ApprovalProcessStep::query()
                ->whereHas('approvalProcess', function($query) use ($group)
                {
                    $query->where('group', $group);
                })
                ->get();
            foreach($steps as $step)
            {
                $rawSteps[$step->step_order] = $step->approver_id;
            }
            $allprocess = ApprovalProcess::query()
                ->where('group', $group)
                ->get();
            foreach($allprocess as $process) $process->steps()->delete();
            foreach($rawSteps as $step_order => $approver_id)
            {
                foreach($allprocess as $process)
                {
                    $process->steps()->create([
                        'step_order' => $step_order,
                        'approver_id' => $approver_id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
