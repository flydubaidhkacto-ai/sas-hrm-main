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
        Schema::create('payslip_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->double('total_amount')->default(0);
            $table->double('paid_amount');
            $table->double('due_amount')->default(0);
            $table->double('previous_balance')->default(0);
            $table->double('new_balance')->default(0);
            $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table){
            $table->double('payroll_balance')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_payments');
        Schema::table('users', function (Blueprint $table){
            $table->dropColumn('payroll_balance');
        });
    }
};
