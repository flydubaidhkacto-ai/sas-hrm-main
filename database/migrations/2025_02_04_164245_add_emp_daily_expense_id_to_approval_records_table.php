<?php

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
        Schema::table('approval_records', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_records', 'emp_daily_expense_id')) {
                $table->foreignId('emp_daily_expense_id')
                    ->nullable()
                    ->constrained('emp_daily_expenses')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_records', function (Blueprint $table) {
            if (Schema::hasColumn('approval_records', 'emp_daily_expense_id')) {
                $table->dropForeign('approval_records_emp_daily_expense_id_foreign');
                $table->dropColumn('emp_daily_expense_id');
            }
        });
    }
};
