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
        Schema::table('emp_daily_expenses', function (Blueprint $table) {
            $table->foreignId('expensed_category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emp_daily_expenses', function (Blueprint $table) {
            try {
                $table->dropForeign(['expensed_category_id']); // Drop the foreign key constraint
                $table->dropColumn('expensed_category_id');    // Drop the column
            } catch (\Exception $e) {
                // Ignore the error if the foreign key constraint does not exist
            }
        });
    }
};
