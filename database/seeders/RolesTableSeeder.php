<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::truncate();
        Role::create(['name' => 'ADMIN']);
        Role::create(['name' => 'WORKSHOP']);
        Role::create(['name' => 'WAREHOUSE']);
        Role::create(['name' => 'OPERATOR']);
        Role::create(['name' => 'PE']);
        Role::create(['name' => 'STORE']);
        Role::create(['name' => 'PPIC']);
        Role::create(['name' => 'MAINTENANCE']);
        Role::create(['name' => 'SECONDPROCESS']);
        Role::create(['name' => 'ASSEMBLYPROCESS']);
    }
}
