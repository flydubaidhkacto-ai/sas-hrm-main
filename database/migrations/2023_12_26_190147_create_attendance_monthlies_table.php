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
        Schema::create('attendance_monthlies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('month');
            $table->foreignId('shift_id');
            $table->time('working_time');
            $table->time('overtime_time')->nullable();
            $table->integer('total_early_check_in')->nullable();
            $table->integer('total_late_check_in')->nullable();
            $table->integer('total_absent')->nullable();
            $table->integer('working_rate')->default(0)->comment("Calculate depending on the total hours of working");
            $table->text('system_notes')->nullable()->comment("To save and show how system handled this attendance");
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
        Schema::dropIfExists('attendance_monthlies');
    }
};
