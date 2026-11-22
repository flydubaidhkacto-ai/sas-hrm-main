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
        try {
            Schema::table('payroll_card_earn_head_items', function (Blueprint $table) {
                $table->foreignId('payroll_card_id')->constrained()->cascadeOnDelete();
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
        try {
            Schema::table('payroll_card_deduction_items', function (Blueprint $table) {
                $table->foreignId('payroll_card_id')->constrained()->cascadeOnDelete();
            });
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            //
        });
    }
};
