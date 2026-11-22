<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 10; $i++) {
            DB::table('users')->insert([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'join_date' => $faker->date(),
                'phone_number' => $faker->phoneNumber,
                'status' => $faker->randomElement(['active', 'inactive']),
                'role' => 'employee',
                'is_active' => true,
                'employee_type' => 'Permanent',
                'avatar' => $faker->imageUrl(),
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'birth_date' => $faker->date(),
                'gender' => $faker->randomElement(['male', 'female']),
                'branch_id' => $faker->numberBetween(1, 5),
                'department_id' => $faker->numberBetween(1, 5),
                'designation_id' => $faker->numberBetween(1, 5),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
