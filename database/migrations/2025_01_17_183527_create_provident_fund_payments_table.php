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
        Schema::create('provident_fund_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('payslip_id');
            $table->date('month_date');
            $table->decimal('basic_salary', 10, 2);
            $table->decimal('balance', 10, 2)->comment('new fund amount from basic salary + previous balance');
            $table->decimal('interest_amount', 10, 2)->comment('interest amount from previous balance_with_interest');
            $table->decimal('interest', 10, 2)->comment('yearly interest rate in percentage');
            $table->decimal('balance_with_interest', 10, 2)->comment('current balance + current interest_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provident_fund_payments');
    }
};
