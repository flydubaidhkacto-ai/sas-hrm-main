<?php

use App\Models\LeaveApplication;
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
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->double('duration_count')->nullable();
            $table->json('applied_dates')->nullable();
        });
        LeaveApplication::query()
            ->chunk(100, function ($leaves) {
                foreach ($leaves as $leave) {
                    if($leave->is_hourly) continue;
                    $duration = LeaveApplication::getDurationDays($leave->start_date, $leave->end_date, $leave->employee);
                    $leave->duration_count = $duration['total'];
                    $leave->applied_dates = $duration['dates'];
                    $leave->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('duration_count');
            $table->dropColumn('applied_dates');
        });
    }
};
