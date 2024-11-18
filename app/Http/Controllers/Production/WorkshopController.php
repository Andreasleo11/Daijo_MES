<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Production\PRD_MouldingJob;
use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_MaterialLog;

class WorkshopController extends Controller
{
    public function handleScanStart(Request $request)
    {   
        // Get the scanned barcode from the request
        $barcode = $request->input('barcode');

        // Split the barcode using '-' as the separator
        if (strpos($barcode, '-') !== false) {
            [$item_code, $item_id] = explode('-', $barcode, 2);
        } else {
            return back()->with('error', 'Invalid barcode format.');
        }
        $scanTime = now('Asia/Jakarta')->format('Y-m-d H:i:s');

        $user = auth()->user();
        // dd($user);

        $data = PRD_BillOfMaterialChild::with('materialProcess', 'parent')->where('id', $item_id)->first();
        
        $materialProcess = $data->materialProcess()
        ->where('process_name', $user->name)
        ->whereNull('scan_in')  // Ensure scan_in is null
        ->orderBy('created_at', 'asc')  // Order by created_at to get the earliest
        ->first();  // Get the first match

        // If a matching material process is found, update its scan_in field with the scan time
        if ($materialProcess) {
            $materialProcess->scan_in = $scanTime;
            $materialProcess->save();  // Save the changes
            $anyScanIn = $data->materialProcess()->whereNotNull('scan_in')->exists();

        // If any materialProcess has scan_in not null, update the status to 'started'
            if ($anyScanIn) {
                $data->status = 'Started';
                $data->save();  // Save the status update
            }
        } else {
            return back()->with('error', 'No matching material process found.');
        }

        PRD_MouldingJob::create([
            'user_id' => $user->id,  // User ID from authenticated user
            'scan_start' => $scanTime,  // The scan start time (scan_in)
            'scan_finish' => null,  // Initially, scan_finish is null
            'created_at' => now(),  // Current timestamp
            'updated_at' => now(),  // Current timestamp
        ]);


        dd('test');
        // Debug the split values
        dd([
            'item_code' => $item_code,
            'item_id' => $item_id,
            'scan_time' => $scanTime,
        ]);
    }
}
