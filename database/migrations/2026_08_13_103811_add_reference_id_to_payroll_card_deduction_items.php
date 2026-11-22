<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_card_deduction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('reference_id')->nullable()->after('amount_type');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_card_deduction_items', function (Blueprint $table) {
            $table->dropColumn('reference_id');
        });
    }
};
