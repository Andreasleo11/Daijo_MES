<?php

namespace Database\Seeders;

use App\Models\Specification;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->truncate();
        DB::table('users')->insert([
            [
                'name' => 'admin',
                'email' => 'admin@daijo.co.id',
                'specification_id' => Specification::where('name', 'ADMIN')->first()->id,
                'remember_token' => null,
                'password' => Hash::make('admin1234'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'joko',
                'email' => 'joko@daijo.co.id',
                'specification_id' => Specification::where('name', 'WAREHOUSE')->first()->id,
                'password' => Hash::make('joko1234'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'CNC',
                'email' => 'cnc@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'EDM',
                'email' => 'edm@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'WIRECUT',
                'email' => 'wirecut@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'QC',
                'email' => 'qc@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'POLISH',
                'email' => 'polish@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'ASSEMBLY',
                'email' => 'assembly@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'MANUAL',
                'email' => 'manual@daijo.co.id',
                'specification_id' => Specification::where('name', 'WORKSHOP')->first()->id,
                'password' => Hash::make('daijo123'),
                'remember_token' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
