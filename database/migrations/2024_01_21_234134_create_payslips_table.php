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
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('month_date');
            $table->double('total_earn_heads');
            $table->double('total_deductions');
            $table->double('basic_salary');
            $table->double('total_salary');
            $table->integer('working_days');
            $table->enum('pay_type', ['hourly', 'daily', 'monthly']);
            $table->foreignId('created_by')->nullable();
            $table->json('earn_heads');
            $table->json('deductions');
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
        Schema::dropIfExists('payslips');
    }
};
