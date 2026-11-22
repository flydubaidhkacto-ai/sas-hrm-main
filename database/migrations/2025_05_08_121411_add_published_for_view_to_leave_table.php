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
            $table->boolean('published_for_view')->default(false);
        });
        Schema::table('approval_process_steps', function (Blueprint $table) {
            $table->boolean('is_publisher')->default(false);
        });
        LeaveApplication::chunk(100, function ($leaves) {
            foreach ($leaves as $leave) {
                $leave->update([
                    'published_for_view' => true,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('published_for_view');
        });
        Schema::table('approval_process_steps', function (Blueprint $table) {
            $table->dropColumn('is_publisher');
        });
    }
};
