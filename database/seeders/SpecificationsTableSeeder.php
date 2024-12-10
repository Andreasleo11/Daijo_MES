<?php

namespace Database\Seeders;

use App\Models\Specification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SpecificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Specification::truncate();
        Specification::create(['name' => 'WORKSHOP']);
        Specification::create(['name' => 'WAREHOUSE']);
        Specification::create(['name' => 'ADMIN']);

    }
}
