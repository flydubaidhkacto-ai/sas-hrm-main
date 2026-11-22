<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Fix asset location data: DAC CTO -> Dhaka CTO, DAC APT -> Dhaka APT
     * Also update location_id to match the correct Super Department IDs.
     */
    public function up(): void
    {
        // Update DAC CTO -> Dhaka CTO (SuperDepartment ID: 4)
        DB::table('assets')
            ->where('location', 'DAC CTO')
            ->update([
                'location' => 'Dhaka CTO',
                'location_id' => 4,
            ]);

        // Update DAC APT -> Dhaka APT (SuperDepartment ID: 5)
        DB::table('assets')
            ->where('location', 'DAC APT')
            ->update([
                'location' => 'Dhaka APT',
                'location_id' => 5,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse: Dhaka CTO -> DAC CTO
        DB::table('assets')
            ->where('location', 'Dhaka CTO')
            ->where('location_id', 4)
            ->update([
                'location' => 'DAC CTO',
                'location_id' => null,
            ]);

        // Reverse: Dhaka APT -> DAC APT
        DB::table('assets')
            ->where('location', 'Dhaka APT')
            ->where('location_id', 5)
            ->update([
                'location' => 'DAC APT',
                'location_id' => null,
            ]);
    }
};
