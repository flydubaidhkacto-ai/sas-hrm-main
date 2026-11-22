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
        Schema::create('auto_absent_fines', function (Blueprint $table) {
            $table->id();
            $table->enum('absent_days', ['consecutive_days', 'any_days_on_month']);
            $table->integer('absent_days_amount');
            $table->integer('fine_amount');
            $table->enum('fine_amount_type', ['fixed', 'days']);
            $table->enum('fine_status', ['approved', 'pending']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_absent_fines');
    }
};
