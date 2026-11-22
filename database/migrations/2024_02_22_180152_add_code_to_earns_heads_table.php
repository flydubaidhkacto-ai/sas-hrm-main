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
        Schema::table('earn_heads', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
        });
        Schema::table('deductions', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
        });
        Schema::table('payroll_card_earn_head_items', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
        });
        Schema::table('payroll_card_deduction_items', function (Blueprint $table) {
            $table->string('code')->after('id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('earn_heads', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('deductions', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('payroll_card_earn_head_items', function (Blueprint $table) {
            $table->dropColumn('code');
        });
        Schema::table('payroll_card_deduction_items', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
