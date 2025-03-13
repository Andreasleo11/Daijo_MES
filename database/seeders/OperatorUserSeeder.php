<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OperatorUser;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class OperatorUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Initialize Faker to generate fake names
        $faker = Faker::create();

        // Create 10 users with random name and password
        for ($i = 0; $i < 10; $i++) {
            OperatorUser::create([
                'name' => $faker->firstName, // Random name
                'password' => Str::random(10), // Random 10-character password, hashed
            ]);
        }
    }
}
