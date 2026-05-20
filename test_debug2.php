<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$latest = DB::table('production_summary')->orderBy('id', 'desc')->limit(10)->get();
foreach ($latest as $s) {
    $scanCount = DB::table('production_scanned_data')->where('summary_id', $s->id)->count();
    echo "ID: {$s->id}, SPK: {$s->spk_code}, Date: {$s->created_date}, WH: {$s->warehouse}, CreatedAt: {$s->created_at}, ScansCount: {$scanCount}\n";
}
