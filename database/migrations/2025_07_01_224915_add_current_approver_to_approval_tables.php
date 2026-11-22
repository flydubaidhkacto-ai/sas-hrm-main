<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $tables = [
        'leave_applications',
        'loans',
        'emp_daily_expenses',
        'inventory_item_requests',
        'asset_requests',
        'fines',
        'attendance_requests'
    ];
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('current_approver_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn('current_approver_id');
            });
        }
    }
};
