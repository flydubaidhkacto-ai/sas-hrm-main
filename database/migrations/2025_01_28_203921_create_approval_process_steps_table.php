<?php

use App\Models\ApprovalProcess;
use App\Models\ApprovalProcessStep;
use App\Models\User;
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
        Schema::create('approval_process_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_process_id')->constrained()->cascadeOnDelete();
            $table->foreignId('approver_id');
            $table->integer('step_order');
            $table->timestamps();
        });

        // make default approval process steps
        $superAdmin = User::where('role', 'super_admin')->latest()->first();
        if (!$superAdmin) {
            return;
        }

        foreach (ApprovalProcess::$types as $type) {
            $approvalProcess = ApprovalProcess::create([
                'type' => $type,
                'for_users' => null,
                'name' => 'Default ' . $type,
            ]);
            if (!$approvalProcess) continue;
            ApprovalProcessStep::create([
                'approval_process_id' => $approvalProcess->id,
                'approver_id' => $superAdmin->id,
                'step_order' => 1,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_process_steps');
    }
};
