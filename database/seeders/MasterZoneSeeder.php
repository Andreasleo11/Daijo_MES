<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterZone;

class MasterZoneSeeder extends Seeder
{
    public function run()
    {
        $zones = ['A', 'B', 'C1', 'C2'];

        foreach ($zones as $zone) {
            MasterZone::firstOrCreate([
                'zone_name' => $zone
            ]);
        }
    }
}
