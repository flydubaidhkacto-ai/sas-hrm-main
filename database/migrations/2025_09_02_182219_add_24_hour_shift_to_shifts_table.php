<?php

use App\Models\Shift;
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
        Schema::table('shifts', function (Blueprint $table) {
            $table->boolean('is_24_hour_shift')->default(false);
            $table->integer('max_working_minutes')->default(0); // in minutes
            $table->integer('max_overtime_minutes')->default(0); // in minutes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['is_24_hour_shift', 'max_working_minutes', 'max_overtime_minutes']);
        });
    }
};
