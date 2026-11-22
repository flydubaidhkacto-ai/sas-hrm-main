<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(OfficialFormSeeder::class);
        // $this->call(EmployeeSeeder::class);
        // $this->call(LiveLocationSeeder::class);
    }
}
