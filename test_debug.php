<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionScannedData;
use App\Models\ProductionSummary;
use Illuminate\Support\Facades\DB;

// Insert fake data
$fakeData = ProductionScannedData::create([
    'spk_code' => 'TEST-123',
    'dic_id' => 1,
    'item_code' => 'ITEM-123',
    'warehouse' => 'FFI',
    'quantity' => 10,
    'label' => 'label1',
    'user' => 'tester',
    'processed' => false,
    'summary_id' => null,
]);

echo "Inserted fake scanned data with ID: {$fakeData->id}\n";

// Run command manually
echo "Running GenerateProductionSummary command...\n";
\Artisan::call('summary:generate');
echo \Artisan::output();

// Check result
$summary = ProductionSummary::where('spk_code', 'TEST-123')->orderBy('id', 'desc')->first();
if ($summary) {
    echo "Generated Summary ID: {$summary->id}\n";
    $scanned = ProductionScannedData::find($fakeData->id);
    echo "Scanned Data after command - Processed: {$scanned->processed}, Summary_ID: {$scanned->summary_id}\n";
    
    // Clean up
    $summary->delete();
    $scanned->delete();
} else {
    echo "No summary generated for TEST-123!\n";
    $fakeData->delete();
}
