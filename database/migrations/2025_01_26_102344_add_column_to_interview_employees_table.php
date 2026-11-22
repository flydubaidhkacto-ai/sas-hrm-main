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
        Schema::table('interview_employees', function (Blueprint $table) {
            $table->double('rating')->nullable()->after('expected_salary');
            $table->text('notes')->nullable()->after('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interview_employees', function (Blueprint $table) {
            $table->dropColumn('rating');
            $table->dropColumn('notes');
        });
    }
};
