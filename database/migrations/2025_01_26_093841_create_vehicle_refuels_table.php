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
        Schema::create('vehicle_refuels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->string('fuel_type');
            $table->string('fuel_station');
            $table->decimal('budget', 10, 2);
            $table->string('place');
            $table->decimal('km_per_unit', 10, 2);
            $table->decimal('last_reading', 10, 2)->nullable();
            $table->decimal('last_unit', 10, 2)->nullable();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->decimal('refuel_limit', 10, 2)->nullable();
            $table->decimal('max_unit', 10, 2);
            $table->decimal('consumption_percent', 5, 2)->nullable();
            $table->decimal('odometer_end_of_day', 10, 2)->nullable();
            $table->decimal('odometer_at_refuel', 10, 2);
            $table->decimal('unit_taken', 10, 2);
            $table->string('fuel_slip_scan_copy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_refuels');
    }
};
