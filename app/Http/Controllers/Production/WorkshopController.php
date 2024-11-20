<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Production\PRD_MouldingJob;
use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_MaterialLog;

class WorkshopController extends Controller
{

    public function updateUsername(Request $request)
    {
        // Validate the input
        $request->validate([
            'username' => 'required|string|max:255',
        ]);

        // Update the username for the currently logged-in user
        $user = auth()->user();
        $user->username = $request->input('username');
        $user->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Your username has been updated successfully.');
    }

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
            $materialProcess->pic = $user->username;
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

        $job = PRD_MouldingJob::create([
            'user_id' => $user->id,  // User ID from authenticated user
            'material_id' => $item_id,
            'scan_start' => $scanTime,  // The scan start time (scan_in)
            'scan_finish' => null,  // Initially, scan_finish is null
            'created_at' => now(),  // Current timestamp
            'updated_at' => now(),  // Current timestamp
        ]);

        $log = PRD_MaterialLog::with('childData', 'childData.parent')->where('scan_in', $job->scan_start)
            ->first();
    

        return redirect()->route('workshop.index');
    }

    public function index()
    {
        $user = auth()->user();
        $job = PRD_MouldingJob::where('user_id', $user->id)
            ->whereNull('scan_finish')
            ->first();

        $log = PRD_MaterialLog::with('childData', 'childData.parent')->where('scan_in', $job->scan_start)
            ->first();

        return view('production.workshop.index', compact('job', 'log'));

    }

    public function updateMaterialChildStatus($materialChildId)
    {
        // Retrieve all processes associated with the given MaterialChild ID where status is 1 (finished)
        $allProcessesFinished = PRD_MaterialLog::where('child_id', $materialChildId)
            ->where('status', 1) // Only check for finished status
            ->count() === PRD_MaterialLog::where('child_id', $materialChildId)->count(); // Check if all related processes are finished

        // If all processes are finished, update the MaterialChild status
        if (!$allProcessesFinished) {
            return; // Skip the update and exit early
        }
    
        // If all processes are finished, update the MaterialChild status
        PRD_BillOfMaterialChild::where('id', $materialChildId)
            ->update(['status' => 'Finished']);
    }

    public function updateAllMaterialChildrenStatus()
    {
        // Retrieve material children with the status 'Not Finished' or something that needs updating
        $materialChildren = PRD_BillOfMaterialChild::where('status', '=', 'Started')->pluck('id');

        foreach ($materialChildren as $materialChildId) {
            $this->updateMaterialChildStatus($materialChildId);
        }
    }

    public function handeScanOut(Request $request)
    {
        $logId = $request->input('log_id');
        $jobId = $request->input('job_id');
        $scanOutTime = now('Asia/Jakarta')->format('Y-m-d H:i:s'); // Current timestamp for scan out

        $log = PRD_MaterialLog::find($logId);
        $job = PRD_MouldingJob::find($jobId);

        $log->scan_out = $scanOutTime; 
        $log->status = '1';
        $log->save();

        $job->scan_finish = $scanOutTime;
        $job->save();

        $this->updateAllMaterialChildrenStatus();
        return redirect()->route('dashboard');
    }
   
}
