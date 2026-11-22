<?php

use App\Models\Attendance;
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
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('late_time')->nullable();
        });
        Attendance::query()
            ->where('got_late_check_in', true)
            ->chunk(100, function ($attendances) {
                foreach ($attendances as $attendance) {
                    $attendance->late_time = late_time_missed($attendance->shift, $attendance->worked_from);
                    $attendance->save();
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('late_time');
        });
    }
};
