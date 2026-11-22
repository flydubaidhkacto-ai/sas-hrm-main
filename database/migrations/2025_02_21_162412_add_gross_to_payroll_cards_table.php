<?php

use App\Models\PayrollCard;
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
        Schema::table('payroll_cards', function (Blueprint $table) {
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable()
                ->comment('calculated from basic salary and earn heads');
            $table->decimal('net_salary', 10, 2)->nullable()
                ->comment('calculated from gross salary and deductions');
        });
        // Schema::table('payroll_cards', function (Blueprint $table) {
        //     // $table->dropColumn('salary');
        // });
        PayrollCard::query()->chunk(100, function ($cards) {
            foreach ($cards as $card) {
                $card->recalculateSalaryItems();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('payroll_cards', function (Blueprint $table) {
        //     // $table->decimal('salary', 10, 2)->nullable();
        // });
        PayrollCard::query()->chunk(100, function ($cards) {
            foreach ($cards as $card) {
                $card->salary = $card->gross_salary;
                $card->save();
            }
        });
        Schema::table('payroll_cards', function (Blueprint $table) {
            $table->dropColumn('gross_salary');
            $table->dropColumn('basic_salary');
            $table->dropColumn('net_salary');
        });
    }
};
