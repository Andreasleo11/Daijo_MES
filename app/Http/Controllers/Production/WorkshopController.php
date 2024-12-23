<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_MaterialLog;
use App\Models\Production\PRD_MouldingUserLog;
use Carbon\Carbon;

class WorkshopController extends Controller
{
    function determineShift($clockIn): ?int
    {
        // Convert the string $clockIn to a Carbon instance
        $clockIn = Carbon::parse($clockIn)->setTimezone('Asia/Jakarta');

        $today = $clockIn->format('Y-m-d');
         // Define shift start and end times
        $shiftStartTimes = [
            1 => Carbon::parse("$today 07:30:00")->setTimezone('Asia/Jakarta'),
            2 => Carbon::parse("$today 15:30:00")->setTimezone('Asia/Jakarta'),
            3 => Carbon::parse("$today 23:30:00")->setTimezone('Asia/Jakarta'),
        ];

        // Define shift end times
        $shiftEndTimes = [
            1 => Carbon::parse("$today 15:30:00")->setTimezone('Asia/Jakarta'),
            2 => Carbon::parse("$today 23:30:00")->setTimezone('Asia/Jakarta'),
            3 => Carbon::parse("$today 07:30:00")->setTimezone('Asia/Jakarta')->addDay(), // Shift 3 ends at 07:30 AM next day
        ];

        // If the clock-in time is before 07:30 AM today, it's part of Shift 3
        if ($clockIn->lt($shiftStartTimes[1])) {
            return 3; // Shift 3
        }

        foreach ($shiftStartTimes as $shift => $startTime) {
            $endTime = $shiftEndTimes[$shift];
            if ($clockIn->between($startTime, $endTime, true)) { // 'true' makes the comparison inclusive
                return $shift;
            }
        }

        return null; // Return null if no shift matches
    }

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
        return redirect()->route('workshop.main.menu')->with('success', 'Your username has been updated successfully.');
    }

    public function handleScanStart(Request $request)
    {   
        // dd($request->all());
        //test
        // $clockIn = Carbon::parse('05:00:00', 'Asia/Jakarta')->format('Y-m-d H:i:s');
        $clockIn = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        // Get the scanned barcode from the request
        $barcode = $request->input('barcode');

        // Split the barcode using '-' as the separator
        if (strpos($barcode, '~') !== false) {
            [$item_code, $item_id] = explode('~', $barcode, 2);
        } else {
            return back()->with('error', 'Invalid barcode format.');
        }
        $scanTime = now('Asia/Jakarta')->format('Y-m-d H:i:s');

        $user = auth()->user();
        

        $data = PRD_BillOfMaterialChild::with('materialProcess', 'parent')->where('id', $item_id)->first();
      
        if ($data->status === 'Canceled') {
            return redirect()->back()->withErrors(['error' => 'Item sudah di cancel']);
        }
        
        $materialProcess = $data->materialProcess()
        ->where('process_name', $user->name)
        ->whereNull('scan_in')  // Ensure scan_in is null
        ->orderBy('created_at', 'asc')  // Order by created_at to get the earliest
        ->first();  // Get the first match

        // If a matching material process is found, update its scan_in field with the scan time
        if ($materialProcess) {
            $materialProcess->scan_in = $scanTime;
            $materialProcess->pic = $user->username;
            $materialProcess->status = 1;
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
        
        $worker = PRD_MouldingUserLog::create([
            'material_log_id' => $materialProcess->id,
            'username' => $user->username,
            'shift' => $this->determineShift($clockIn),
        ]);

        
        return redirect()->route('workshop.main.menu');
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
        $childId = $request->input('child_id');
        $verifydata = PRD_BillOfMaterialChild::find($childId);
        $verifiedbarcode = $verifydata->item_code . '~' . $verifydata->id;
        $barcode = $request->input('scan_out');

        if ($verifiedbarcode !== $barcode) {
            return redirect()->back()->with('error', 'Barcode salah brow');
        }

        $scanOutTime = now('Asia/Jakarta')->format('Y-m-d H:i:s'); // Current timestamp for scan out

        $log = PRD_MaterialLog::find($logId);

        $log->scan_out = $scanOutTime; 
        $log->status = '2';
        $log->save();

        $this->updateAllMaterialChildrenStatus();
        return redirect()->route('workshop.main.menu');
    }


    public function index($id)
    {
        
        $user = auth()->user();

        $log = PRD_MaterialLog::with('childData', 'childData.parent', 'childData.materialProcess')->find($id);
        // Initialize an array to hold the process names and statuses
        
        $allprocess = $log->childData->materialProcess;
       
        
        $workers = PRD_MouldingUserLog::where('material_log_id', $id)->get();

        return view('production.workshop.index', compact('log', 'workers', 'allprocess'));

    }

    public function mainMenuByWorkshop()
    {
        $user = auth()->user();
        $logs = PRD_MaterialLog::with('childData')->where('process_name', $user->name)->whereNotNull('scan_in')->get();
        // dd($jobs);
        $this->updateAllMaterialChildrenStatus();
        return view('production.workshop.mainmenu', compact('logs', 'user'));
    }


    public function addWorker(Request $request)
    {
        //test
        // $clockIn = Carbon::parse('23:40:00', 'Asia/Jakarta')->format('Y-m-d H:i:s');
        // dd($clockIn);
        $clockIn = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
        // Validate the incoming request data
        $request->validate([
            'username' => 'required|string|max:255', // Adjust validation as needed
            'log_id' => 'required|exists:prd_material_logs,id', // Ensure the job exists in the jobs table
            'job' => 'nullable|string|max:255', // Validate the job field (you can adjust this if you want to enforce specific jobs)
            'remark_worker' => 'nullable|string|max:500', // Validate the remark_worker (optional field)
        ]);

        // Create a new worker and associate it with the job
        PRD_MouldingUserLog::create([
            'material_log_id' => $request->input('log_id'),
            'username' => $request->input('username'),
            'shift' => $this->determineShift($clockIn),
            'jobs' => $request->input('job'), // Save the job
            'remark' => $request->input('remark_worker'),
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Worker added successfully.');
    }
   

    public function summaryDashboard()
    {
        $datas = PRD_MaterialLog::with('childData', 'workers', 'childData.parent')->paginate(10);
       
        return view('production.workshop.summarydashboard', compact('datas'));
    }

    public function setScanStart(Request $request)
    {
        $log = PRD_MaterialLog::find($request->log_id);

        // Set the scan_start to current Jakarta time (timezone 'Asia/Jakarta')
        if (is_null($log->scan_start)) {
            $log->scan_start = Carbon::now('Asia/Jakarta');
            $log->save();
        }

        return redirect()->route('workshop.index', ['id' => $log->id]); // Redirect to the same page (or another route you prefer)
    }

    public function storeRemark(Request $request, $log_id)
    {
        // Validate input
        $request->validate([
            'remark' => 'required|string|max:255',
        ]);

        // Find the log and update its remark
        $log = PRD_MaterialLog::findOrFail($log_id);
        $log->remark = $request->remark;
        $log->save();

        // Redirect back to the log show page
        return redirect()->route('workshop.index', ['id' => $log->id]);
    }


    public function updateWorker(Request $request)
    {
      
        // Validate the input data
        $request->validate([
            'worker_id' => 'required|exists:prd_moulding_user_logs,id', // Ensure worker exists
            'username' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'remark' => 'nullable|string|max:255',
        ]);
        
        // Find the worker by ID and update the details
        $worker = PRD_MouldingUserLog::find($request->worker_id);
        $worker->username = $request->username;
        $worker->jobs = $request->job;
        $worker->remark = $request->remark;
        $worker->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Worker details updated successfully.');
    }
    

    public function removeScanIn($id)
    {
        $log = PRD_MaterialLog::find($id);
        
        // Your logic to remove the scan_in goes here
        // For example:
        $log->scan_in = null;  // Remove scan_in or reset as needed
        $log->save();

        return redirect()->route('workshop.main.menu')->with('status', 'Scan In removed successfully!');
    }

}
