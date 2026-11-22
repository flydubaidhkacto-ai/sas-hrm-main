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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id');
            $table->date('date');
            $table->foreignId('shift_id');
            $table->dateTime('worked_from')->nullable();
            $table->dateTime('worked_to')->nullable();
            $table->time('working_time')->default("00:00:00");
            $table->dateTime('overtime_from')->nullable();
            $table->dateTime('overtime_to')->nullable();
            $table->time('overtime_time')->nullable();
            $table->boolean('did_early_check_in')->default(false);
            $table->boolean('got_late_check_in')->default(false);
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
        Schema::dropIfExists('attendances');
    }
};
