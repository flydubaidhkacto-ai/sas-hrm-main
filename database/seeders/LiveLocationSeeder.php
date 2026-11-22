<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;


class LiveLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Approximate coordinates for Dhaka, Bangladesh
        $minLatitude = 23.7;
        $maxLatitude = 23.9;
        $minLongitude = 90.3;
        $maxLongitude = 90.5;

        // Number of records to insert
        $numOfRecords = 100;

        for ($i = 0; $i < $numOfRecords; $i++) {
            DB::table('live_locations')->insert([
                'user_id' => $faker->numberBetween(1001, 1001), // Random user ID between 1000 and 1010
                'latitude' => $faker->randomFloat(6, $minLatitude, $maxLatitude), // Latitude within Dhaka
                'longitude' => $faker->randomFloat(6, $minLongitude, $maxLongitude), // Longitude within Dhaka
                'ui_id' => $faker->uuid, // Random UUID
                'device_name' => $faker->word, // Random device name
                'platform' => $faker->randomElement(['Android', 'iOS', 'Web']), // Random platform
                'date_time' => $faker->dateTimeBetween('2025-02-01', '2025-02-28'), // Date within February
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "$numOfRecords records inserted into live_locations table.\n";
    }
}
