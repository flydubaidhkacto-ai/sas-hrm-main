<?php

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
        Schema::create('emp_daily_expenses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->double('amount');
            $table->date('date');

            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->foreignId('processed_by')->nullable();
            $table->dateTime('final_approved_at')->nullable();
            $table->dateTime('final_rejected_at')->nullable();
            $table->foreignId('final_processed_by')->nullable();

            $table->foreignId('paid_payslip_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('emp_daily_expenses');
    }
};
