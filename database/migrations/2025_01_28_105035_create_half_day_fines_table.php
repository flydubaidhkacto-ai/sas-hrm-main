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
        Schema::create('half_day_fines', function (Blueprint $table) {
            $table->id();
            $table->double('minutes');
            $table->double('deduction_amount');
            $table->enum('amount_type', ['fixed', 'basicPercent', 'workingHour']);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('half_day_fines');
    }
};
