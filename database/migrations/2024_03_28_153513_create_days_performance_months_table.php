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
        Schema::create('days_performance_months', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_of_month_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_category_id')->constrained()->cascadeOnDelete();
            $table->dateTime('date');
            $table->double('value');
            $table->double('max_value');
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
        Schema::dropIfExists('days_performance_months');
    }
};
