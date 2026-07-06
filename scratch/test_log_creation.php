<?php

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\ProductionOutputLog;
use App\Http\Controllers\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// 1. Login as user 49 (0850B)
$user = User::find(49);
if (!$user) {
    echo "User 49 not found\n";
    exit(1);
}
Auth::login($user);
echo "Logged in as: " . $user->name . "\n";

// Print active job details
$itemCode = $user->jobs->item_code ?? null;
echo "Active job item_code: " . ($itemCode ?? 'NULL') . "\n";

// 2. Clear existing output logs for testing clean state
ProductionOutputLog::truncate();
echo "Truncated production_output_logs table\n";

// 3. Create request
$request = new Request();
$request->merge(['operator_name' => 'Monica']);

// 4. Instantiate controller and call storeOutputLog
$controller = new DashboardController();

try {
    $response = $controller->storeOutputLog($request);
    echo "Controller call completed.\n";
} catch (\Exception $e) {
    echo "Exception occurred: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}

// 5. Check if log is created
$log = ProductionOutputLog::first();
if ($log) {
    echo "Log successfully created:\n";
    echo "ID: " . $log->id . "\n";
    echo "DIC ID: " . $log->dic_id . "\n";
    echo "Operator Name: " . $log->operator_name . "\n";
    echo "Quantity: " . $log->quantity . "\n";
    echo "Logged At (WIB): " . $log->logged_at->format('Y-m-d H:i:s') . "\n";
} else {
    echo "Failed to create log!\n";
}
