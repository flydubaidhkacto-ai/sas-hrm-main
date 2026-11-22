<?php

use App\Models\LeaveApplication;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dateTime('final_approved_at')->nullable();
            $table->dateTime('final_rejected_at')->nullable();
            $table->foreignId('final_processed_by')->nullable();
        });
        LeaveApplication::query()
            ->chunk(100, function ($leaveApplications) {
                foreach ($leaveApplications as $leaveApplication) {
                    $leaveApplication->final_approved_at = $leaveApplication->approved_at;
                    $leaveApplication->final_rejected_at = $leaveApplication->rejected_at;
                    $leaveApplication->final_processed_by = $leaveApplication->processed_by;
                    $leaveApplication->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn(['final_approved_at', 'final_rejected_at', 'final_processed_by']);
        });
    }
};
