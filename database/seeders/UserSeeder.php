<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'joko',
                'email' => 'joko@daijo.co.id',
                'role_name' => 'WAREHOUSE',
                'password' => Hash::make('joko1234'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'CNC',
                'email' => 'cnc@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'EDM',
                'email' => 'edm@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'WIRECUT',
                'email' => 'wirecut@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'QC',
                'email' => 'qc@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'POLISH',
                'email' => 'polish@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'ASSEMBLY',
                'email' => 'assembly@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MANUAL',
                'email' => 'manual@daijo.co.id',
                'role_name' => 'WORKSHOP',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('daijo123'),
                'remember_token' => Str::random(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
