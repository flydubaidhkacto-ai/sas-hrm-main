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
        Schema::create('payroll_card_deduction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_card_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string("category");
            $table->double("amount");
            $table->enum('amount_type', ['fixed', 'percentage', 'other']);
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
        Schema::dropIfExists('payroll_card_deduction_items');
    }
};
