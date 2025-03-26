<?php

namespace App\Http\Controllers;

use App\Events\ParentDataUpdated;
use App\Models\DailyItemCode;
use App\Models\File;
use App\Models\MachineJob;
use App\Models\MasterListItem;
use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_BillOfMaterialParent;
use App\Models\Production\PRD_MaterialLog;
use App\Models\Production\PRD_MouldingJob;
use App\Models\Production\PRD_MouldingUserLog;
use App\Models\ProductionReport;
use App\Models\ProductionScannedData;
use App\Models\MouldChangeLog;
use App\Models\AdjustMachineLog;
use App\Models\SpkMaster;
use App\Models\OperatorUser;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\Auth;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\ValidationException;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role->name === 'ADMIN') {
            return view('dashboards.dashboard-admin');
        } elseif ($user->role->name === 'OPERATOR') {
            $files = collect();
            $machineJobShift = null;
            $itemCode = null;
            $uniquedata = collect();
            $machinejobid = MachineJob::where('user_id', $user->id)->first() ?? null;
            if(count($uniquedata) > 0){
                $dataWithSpkNo = ProductionReport::where('spk_no', $uniquedata[0]['spk'])->first();
            } else {
                $dataWithSpkNo = null;
            }

            $datas = DailyItemCode::where('user_id', $user->id)
                ->whereDate('schedule_date', Carbon::today())
                ->with('masterItem')
                ->get();

            $itemCollections = [];

            foreach ($datas as $data) {
                $itemCode = $user->jobs->item_code ?? null;


                if ($itemCode) {
                    $files = File::where('item_code', $itemCode)->get();
                }

                $itemCodeall = $data->item_code;
                $quantity = $data->quantity;

                // Create an array for each unique item_code
                if (!isset($itemCollections[$itemCodeall])) {
                    $itemCollections[$itemCodeall] = [];
                }

                // Get all SPK records for the current item_code
                $spkRecords = SpkMaster::where('item_code', $itemCodeall)->get();
                $masterItem = MasterListItem::where('item_code', $itemCodeall)->first();
                $perpack = $masterItem->standart_packaging_list ?? 1; // Avoid division by zero

                $labelstart = 0;
                $previous_spk = null;
                $start_label = null;

                foreach ($spkRecords as $spk) {
                    $available_quantity = $spk->planned_quantity - $spk->completed_quantity;

                    if ($quantity <= 0) {
                        break; // Move to the next item_code once the quantity is fulfilled
                    }

                    if ($quantity <= $available_quantity) {
                        $available_quantity = $quantity;
                    }

                    if ($spk->completed_quantity === 0) {
                        $labelstart = 0;
                    } else {
                        $labelstart = ceil($spk->completed_quantity / $perpack);
                    }

                    while ($available_quantity > 0) {
                        $labelstart++;
                        $pack_quantity = min($perpack, $available_quantity);
                        $key = $spk->spk_number . '|' . $spk->item_code;

                        if (isset($itemCollections[$itemCodeall][$key])) {
                            $itemCollections[$itemCodeall][$key]['count']++;
                            $itemCollections[$itemCodeall][$key]['end_label'] = $labelstart;
                        } else {
                            $itemCollections[$itemCodeall][$key] = [
                                'spk' => $spk->spk_number,
                                'item_code' => $spk->item_code,
                                'item_perpack' => $perpack,
                                'available_quantity' => $available_quantity,
                                'count' => 1,
                                'start_label' => $labelstart,
                                'end_label' => $labelstart,
                                'scannedData' => 0, // Will be updated later
                            ];
                        }

                        $available_quantity -= $pack_quantity;
                        $quantity -= $pack_quantity;
                    }
                }
            }

            // Fetch scanned data for all collected SPKs
            foreach ($itemCollections as $itemCodeall => &$spkList) {
                foreach ($spkList as &$spkData) {
                    $spkData['scannedData'] = ProductionScannedData::where('spk_code', $spkData['spk'])
                        ->where('item_code', $spkData['item_code'])
                        ->count();

                    $spkData['totalquantity'] = ProductionScannedData::where('spk_code', $spkData['spk'])
                        ->where('item_code', $spkData['item_code'])
                        ->sum('quantity'); // Summing the 'quantity' column
                }
            }
            // dd($itemCollections);
            return view('dashboards.dashboard-operator', compact('files', 'datas', 'itemCode', 'uniquedata', 'machineJobShift', 'dataWithSpkNo', 'machinejobid', 'itemCollections'));
        } elseif ($user->role->name === 'WORKSHOP') {
            return view('dashboards.dashboard-workshop', compact('user'));
        } else {
            return view('dashboard', compact('user'));
        }
    }

    // public function updateData()
    // {
    //     $parents = PRD_BillOfMaterialParent::all();
    //     $childs = PRD_BillOfMaterialChild::all();
    //     $materialLogs = PRD_MaterialLog::all();
    //     $mouldingUserLogs = PRD_MouldingUserLog::all();

    //     event(new ParentDataUpdated($parents, $childs, $materialLogs, $mouldingUserLogs));
    // }

    public function updateEmployeeName(Request $request)
    {
        $machineJob = MachineJob::where('user_id', auth()->user()->id)->first();

        // Update the employee_name
        $machineJob->employee_name = $request->input('employee_name');
        $machineJob->save();

        // Redirect back or wherever needed
        return redirect()->back()->with('success', 'Employee name updated successfully.');
    }

    public function updateMachineJob(Request $request)
    {
        // Validate the input
        $request->validate([
            'item_code' => 'required|string|max:255',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Get the item code from the form input
        $itemCode = $request->input('item_code');

        // Find the DailyItemCode records for the user
        $verified_data = DailyItemCode::where('user_id', $user->id)->get();

        // Check if the item code exists for the user
        $itemCodeExists = $verified_data->contains('item_code', $itemCode);

        if ($itemCodeExists) {
            // Retrieve the specific DailyItemCode for the item code
            $dailyItemCode = DailyItemCode::where('item_code', $itemCode)->first();

            // Get the current time
            $currentTime = now();

            // // Check if the current time is not within the range of start_time and end_time
            // if ($currentTime->lt($dailyItemCode->start_time) && $currentTime->gt($dailyItemCode->end_time)) {
            //     $startTime = \Carbon\Carbon::parse($dailyItemCode->start_time)->format('H:i');
            //     $endTime = \Carbon\Carbon::parse($dailyItemCode->end_time)->format('H:i');

            //     // Return with an error message if the current time is outside the range
            //     return redirect()
            //         ->back()
            //         ->withErrors(['item_code' => 'The item code is not valid for the current time.'])
            //         ->withInput()
            //         ->with('error', "The current time is outside the shift time range ($startTime-$endTime) for this item code.");
            // }

            // Find the machine job record related to the user
            $machineJob = MachineJob::where('user_id', $user->id)->first();

            if ($machineJob) {
                // Update the machine job with the new item_code
                $machineJob->item_code = $itemCode;

                $currentDateTime = Carbon::now('Asia/Bangkok'); // Get the current date and time
                $dailyItemCodes = DailyItemCode::where('user_id', auth()->user()->id)->get();
                // Loop through the DailyItemCode records

                foreach ($dailyItemCodes as $dailyItemCode) {
                    // Combine the start_date with start_time and end_date with end_time
                    $startDateTime = Carbon::parse($dailyItemCode->start_date . ' ' . $dailyItemCode->start_time, 'Asia/Bangkok');
                    $endDateTime = Carbon::parse($dailyItemCode->end_date . ' ' . $dailyItemCode->end_time, 'Asia/Bangkok');

                    // Check if the current time falls between the start and end time
                    if ($currentDateTime->between($startDateTime, $endDateTime)) {
                        // dd($currentDateTime);
                        // Assign the shift from the matching DailyItemCode
                        $machineJob->shift = $dailyItemCode->shift;
                        break; // Exit the loop once a matching shift is found
                    }
                }

                // If no matching shift is found, you can set a default value if needed
                if (!isset($machineJob->shift)) {
                    return redirect()->back()->with('error', 'No matching shift found!');
                }

                $machineJob->save();

                return redirect()->back()->with('success', 'Machine job updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Machine job not found.');
            }
        } else {
            // Return an error message if the item code does not exist for the user
            return redirect()
                ->back()
                ->withErrors(['item_code' => 'Item code does not exist for the user.'])
                ->withInput()
                ->with('error', 'Item code does not exist for the user.');
        }
    }

    public function itemCodeBarcode($item_code, $quantity)
    {
        try {
            // Fetch SPK data for the given item code
            $datas = SpkMaster::where('item_code', $item_code)->get();

            if ($datas->isEmpty()) {
                return redirect()->back()->with('error', 'No SPK data found for the given item code.');
            }

            // Fetch master item data
            $masteritem = MasterListItem::where('item_code', $item_code)->first();

            if (!$masteritem) {
                return redirect()->back()->with('error', 'No master item found for the given item code.');
            }

            // Get the standard packaging list value
            $perpack = $masteritem->standart_packaging_list;

            if (!$perpack || $perpack == 0) {
                return redirect()->back()->with('error', 'Standard packaging list (per pack) is invalid or zero.');
            }

            // Calculate the number of labels needed
            $label = (int) ceil($quantity / $perpack);
            $uniquedata = [];
            $previous_spk = null; // Variable to track the previous SPK
            $start_label = null; // Variable to store start_label for each SPK

            $labels = []; // Initialize labels array

            foreach ($datas as $data) {
                $available_quantity = $data->planned_quantity - $data->completed_quantity;

                // Check if the available quantity is sufficient
                if ($available_quantity <= 0) {
                    continue; // Skip this SPK as there's no available quantity
                }

                if ($quantity <= $available_quantity) {
                    $available_quantity = $quantity;
                }

                $deficit = 0;
                if ($data->completed_quantity === 0) {
                    $labelstart = 0;
                } else {
                    $labelstart = ceil($data->completed_quantity / $perpack);
                }

                if ($deficit != 0) {
                    $available_quantity -= $deficit;
                    $deficit = 0;
                }

                while ($available_quantity > 0 && $quantity > 0) {
                    if ($available_quantity >= $perpack && $quantity >= $perpack) {
                        // Assign a full label to this SPK
                        $labelstart++;
                        $labels[] = [
                            'spk' => $data->spk_number,
                            'item_code' => $data->item_code,
                            'item_name' => $masteritem->item_name,
                            'warehouse' => 'FG',
                            'quantity' => $perpack,
                            'label' => $labelstart,
                        ];

                        // Check if SPK has changed
                        if ($previous_spk !== $data->spk_number) {
                            // If SPK has changed, set start_label and reset end_label
                            $start_label = $labelstart;
                            $previous_spk = $data->spk_number;
                        }

                        $key = $data->spk_number . '|' . $data->item_code;
                        if (isset($uniquedata[$key])) {
                            $uniquedata[$key]['count']++;
                            $uniquedata[$key]['end_label'] = $labelstart; // Update end_label as it progresses
                        } else {
                            $uniquedata[$key] = [
                                'spk' => $data->spk_number,
                                'item_code' => $data->item_code,
                                'item_name' => $masteritem->item_name,
                                'count' => 1,
                                'start_label' => $start_label, // Set start_label for this SPK
                                'end_label' => $labelstart, // Initially, end_label is the same as start_label
                            ];
                        }

                        $available_quantity -= $perpack;
                        $quantity -= $perpack;
                    } else {
                        // Assign a partial label to this SPK and move to the next
                        $labelstart++;
                        $labels[] = [
                            'spk' => $data->spk_number,
                            'item_code' => $data->item_code,
                            'item_name' => $masteritem->item_name,
                            'warehouse' => 'FG',
                            'quantity' => $available_quantity, // Use remaining available quantity
                            'label' => $labelstart,
                        ];

                        $key = $data->spk_number . '|' . $data->item_code;
                        if (isset($uniquedata[$key])) {
                            $uniquedata[$key]['count']++;
                            $uniquedata[$key]['end_label'] = $labelstart; // Update end_label for partial labels
                        } else {
                            $uniquedata[$key] = [
                                'spk' => $data->spk_number,
                                'item_code' => $data->item_code,
                                'item_name' => $masteritem->item_name,
                                'count' => 1,
                                'start_label' => $start_label,
                                'end_label' => $labelstart,
                            ];
                        }
                        $deficit = $available_quantity;
                        $quantity -= $available_quantity;
                        $available_quantity = 0;
                    }
                }

                if ($quantity <= 0) {
                    break; // Exit the loop if the required quantity has been processed
                }
            }

            if (empty($labels)) {
                return redirect()->back()->with('error', 'No labels were generated. Please check the available quantity and try again.');
            }

            // Convert uniquedata to array format
            $uniquedata = array_values($uniquedata);

            // Generate barcodes
            $barcodeGenerator = new DNS1D();
            $qrCodeWriter = new PngWriter();
            $barcodes = [];
            $qrcodes = [];
            foreach ($labels as $labelData) {
                // First barcode with all data
                $barcodeData1 = implode("\t", [$labelData['spk'], $labelData['item_code'], $labelData['warehouse'], $labelData['quantity'], $labelData['label']]);
                
                // Second barcode with subset of data
                $barcodeData2 = implode("\t", [$labelData['item_code'], $labelData['warehouse'], $labelData['quantity'], $labelData['label']]);

                //BARCODE SIZE IS 1 , 25

                $barcodes[] = [
                    'first' => $barcodeGenerator->getBarcodeHTML($barcodeData1, 'C128', 1, 50),
                    'second' => $barcodeGenerator->getBarcodeHTML($barcodeData2, 'C128', 1, 55),
                ];
                $qrCodeData = implode("\t", [$labelData['spk'], $labelData['warehouse'], $labelData['quantity'], $labelData['label']]);
                // dd($qrCodeData);
                $qrCode = new QrCode(data: $qrCodeData, errorCorrectionLevel: ErrorCorrectionLevel::Medium, size: 70,
                margin: 5);
              
                
    
                // Create the QR code image with PngWriter
                $writer = new PngWriter();
                $qrCodeResult = $writer->write($qrCode);
              
                // Get the PNG image as a string
                $qrCodeImage = $qrCodeResult->getString();
                
                // Base64 encode the image to embed in HTML
                $qrcodes[] = base64_encode($qrCodeImage);

            }

            return view('barcodeMachineJob', compact('labels', 'barcodes', 'qrcodes'));
        } catch (\Exception $e) {
            // Optionally log the error
            // Log::error('Error generating barcodes: ' . $e->getMessage());

            // Return error message to the user
            return redirect()
                ->back()
                ->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }


    public function procesProductionBarcodes(Request $request)
    {
        // dd($request->all());
        // Decode the JSON input from the request
        $datas = json_decode($request->input('datas'), true);
        $uniquedata = json_decode($request->input('uniqueData'));
        // dd($uniquedata);

          // Retrieve the values from the request for creating scanned data
          $spk_code = $request->input('spk_code');
          $quantity = $request->input('quantity');
          $warehouse = $request->input('warehouse');
          $label = $request->input('label');
          $user = $request->input('nik');
          if (!$user) {
              $user = session('verifiedNIK'); // Retrieve from session if not in request\
          
          }
      
     
  
  
          $item_code_spk = SpkMaster::where('spk_number', $spk_code)->first();
  
        // dd($datas);
        // Restructure the unique data based on item_code
        $restructureduniquedata = [];
        foreach ($uniquedata as $itemCode => $spkData) {
            foreach ($spkData as $key => $data) {
                // Store each SPK entry in an array instead of overwriting
                $restructureduniquedata[$itemCode][$key] = $data;
            }
        }

        // dd($restructureduniquedata);

        $dic_id = null;
        foreach ($datas as $data) {
            // dd($data);
            if ($data['item_code'] === $item_code_spk->item_code) {
                $dic_id = $data['id'];  // Set dic_id to the matched data's id

                break; // Exit the loop once the match is found
            }

        }
        // Validate that a matching dic_id was found
        if (!$dic_id) {
            return redirect()->back()->withErrors(['error' => 'Item code not found in datas or no matching dic_id.']);
        }



        // Validate incoming request data
        $request->validate([
            'spk_code' => 'required|string',
            'warehouse' => 'required|string',
            'quantity' => 'required|integer',
            'label' => 'required|string',
        ]);


        // Validation logic for SPK code and label ranges
        $validator = Validator::make($request->all(), [
            'spk_code' => 'required|string',
            'warehouse' => 'required|string',
            'quantity' => 'required|integer',
            'label' => 'required|string',
        ]);

        $validator->after(function ($validator) use ($request, $restructureduniquedata, $item_code_spk) {
            $spk_code = $request->input('spk_code');
            $item_code = $item_code_spk->item_code;
            $label = (int) $request->input('label');
        
            // Check if the provided item_code exists in restructureduniquedata
            $foundSPKs = $restructureduniquedata[$item_code] ?? null;
        
            if (!$foundSPKs) {
                $validator->errors()->add('spk_code', 'The provided SPK code or item code does not exist.');
            } else {
                $isValidLabel = false;
                $validRanges = [];
        
                foreach ($foundSPKs as $spkData) {
                    if ($spkData->spk === $spk_code) { // Use -> instead of []
                        $start_label = (int) $spkData->start_label;
                        $end_label = (int) $spkData->end_label;
        
                        // Store the valid ranges for error messages
                        $validRanges[] = "$start_label - $end_label";
        
                        if ($label >= $start_label && $label <= $end_label) {
                            $isValidLabel = true;
                            break;
                        }
                    }
                }
        
                if (!$isValidLabel) {
                    $validRangesText = implode(', ', $validRanges);
                    $validator->errors()->add('label', "The label must be within the valid range(s) for SPK $spk_code and item code $item_code. Valid range(s): $validRangesText.");
                }
            }
        });
        

        // Return validation errors if validation fails
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

      
        

        // Check if the same scan already exists in the database
        $existingScan = ProductionScannedData::where('spk_code', $spk_code)
            ->where('item_code', $item_code_spk->item_code)
            ->where('label', $label)
            ->first();
        

        if ($existingScan) {
            return redirect()->back()->withErrors(['error' => 'Data already scanned']);
        }

        // Create a new ProductionScannedData entry
        ProductionScannedData::create([
            'spk_code' => $spk_code,
            'dic_id' => $dic_id,  // The associated data ID
            'item_code' => $item_code_spk->item_code,
            'quantity' => $quantity,
            'warehouse' => $warehouse,
            'label' => $label,
            'user' => $user,
        ]);

        // Redirect back to the dashboard with a success message
        return redirect()->route('dashboard')->with('deactivateScanMode', false);
    }

    public function finishJob(Request $request) {}

    public function resetJobs(Request $request)
    {
        dd($request->all());
        $uniquedata = json_decode($request->input('uniqueData'), true);

        $datas = json_decode($request->input('datas'));

        // dd($uniquedata);
        // dd($datas);

        foreach ($uniquedata as $uniquedatum) {
            $targetQuantity = $uniquedatum['count'];
            $actualProductionQuantity = $uniquedatum['scannedData'];

            if ($actualProductionQuantity < $targetQuantity) {
                $dataSendToPpic = [
                    'machine_id' => auth()->user()->id,
                    'spk_no' => $uniquedatum['spk'],
                    'target' => $uniquedatum['count'],
                    'scanned' => $uniquedatum['scannedData'],
                    'outstanding' => $uniquedatum['count'] - $uniquedatum['scannedData'],
                ];

                $dataWithSpkNo = ProductionReport::where('spk_no', $uniquedatum['spk'])->first();
                if ($dataWithSpkNo) {
                    $dataWithSpkNo->update($dataSendToPpic);
                    return redirect()
                        ->back()
                        ->with('success', "Successfully updating spk number $dataWithSpkNo->spk_no");
                } else {
                    ProductionReport::create($dataSendToPpic);
                    // Send mail notification
                    $ppicUser = User::where('name', 'budiman')->first();
                    $ppicUser->notify(new \App\Notifications\ProductionReportCreated($dataSendToPpic));
                }
            }
        }

        // Get the current time (or a specific time if needed)
        $currentTime = now(); // For current time, or you can use Carbon::parse('specific-time')
        dd($currentTime);
        // Define shift times
        $shift1Start = Carbon::parse('07:30:00');
        $shift1End = Carbon::parse('15:30:00');
        $shift2Start = Carbon::parse('15:31:00');
        $shift2End = Carbon::parse('23:30:00');
        $shift3Start = Carbon::parse('23:31:00');
        $shift3End = Carbon::parse('07:29:59');

        // Determine the shift based on the current time
        $shift = null;

        if ($currentTime->between($shift1Start, $shift1End)) {
            $shift = 1;
        } elseif ($currentTime->between($shift2Start, $shift2End)) {
            $shift = 2;
        } elseif ($currentTime->between($shift3Start, $shift3End) || $currentTime->lessThan($shift1Start)) {
            $shift = 3;
        }
        dd($shift);
        // Reset the machine job
        $machineJob = MachineJob::where('user_id', auth()->user()->id)->first();
        $machineJob->update([
            'item_code' => null,
            'shift' => $shift,
        ]);

        return redirect()
            ->back()
            ->with([
                'success' => 'Data sent to PPIC!',
                'deactivateScanMode' => true, // Add this flag
            ]);

        foreach ($uniquedata as $spk) {
            $real_spk = SpkMaster::where('spk_number', $spk['spk'])->first();
            // dd($spk);
            $count = $spk['count']; // Assuming 'count' exists in $spk
            $item_perpack = $spk['item_perpack']; // Assuming 'item_perpack' exists in $spk

            if ($spk['start_label'] !== 1) {
                $newCompletedQuantity = $real_spk->completed_quantity + $count * $item_perpack;
                dd($newCompletedQuantity);
                dd($real_spk);

                $real_spk->completed_quantity = $newCompletedQuantity;
                $real_spk->save(); // Save the updated record
            } else {
                $completedQuantity = $count * $item_perpack;
                dd($completedQuantity);
                $real_spk->completed_quantity = $newCompletedQuantity;
                $real_spk->save();
            }
            // dd($spk);
        }

        // Find all jobs related to the user
        $jobs = MachineJob::where('user_id', auth()->user()->id)->get();

        // Loop through the jobs and reset the item_code (or other relevant fields)
        foreach ($jobs as $job) {
            $job->item_code = null; // Or any default value you'd like
            $job->save(); // Save the changes to the database
        }
        // Optionally return a message or redirect the user
        return redirect()->back()->with('success', 'Jobs have been reset successfully.');
    }

    public function dashboardPlastic()
    {
        $datas = DailyItemCode::with('machinerelation', 'user', 'scannedData')->get();
        //    dd($datas);

        return view('dashboard_plasticinjection', compact('datas'));
    }

    public function resetJob()
    {
        $currentTime = now(); // For current time, or you can use Carbon::parse('specific-time')
        // Define shift times
        $shift1Start = Carbon::parse('07:30:00');
        $shift1End = Carbon::parse('15:30:00');
        $shift2Start = Carbon::parse('15:31:00');
        $shift2End = Carbon::parse('23:30:00');
        $shift3Start = Carbon::parse('23:31:00');
        $shift3End = Carbon::parse('07:29:59');

        // Determine the shift based on the current time
        $shift = null;

        if ($currentTime->between($shift1Start, $shift1End)) {
            $shift = 1;
        } elseif ($currentTime->between($shift2Start, $shift2End)) {
            $shift = 2;
        } elseif ($currentTime->between($shift3Start, $shift3End) || $currentTime->lessThan($shift1Start)) {
            $shift = 3;
        }

        // Reset the machine job
        MachineJob::where('user_id', auth()->user()->id)->update([
            'item_code' => null,
            'shift' => $shift,
        ]);

        return redirect()->back()->with('success', 'Job has been resetted!');
    }

    public function startMouldChange(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::now()->format('Y-m-d');

        $request->validate([
            'pic_name' => 'required|string|max:255',
        ]);

        $currentItemCode = MachineJob::where('user_id', $userId)->value('item_code');

        $operatorUser = OperatorUser::where('name',$request->pic_name)->first();
        
        // Get all item_codes for today, ordered by shift or time
        $dailyItems = DailyItemCode::where('user_id', $userId)
            ->whereDate('start_date', $today) // Match today's records
            ->orderBy('start_time', 'asc') // Order by shift timing
            ->pluck('item_code')
            ->toArray(); // Convert to an array for easier processing

     

        // Find the next item_code in sequence
        $nextItemCode = null;
        $currentIndex = array_search($currentItemCode, $dailyItems);

        if ($currentIndex !== false && isset($dailyItems[$currentIndex + 1])) {
            $nextItemCode = $dailyItems[$currentIndex + 1]; // Get the next item
        } else {
            // Special case: Find the first item_code of the next day
            $nextDay = Carbon::tomorrow()->format('Y-m-d'); // Get tomorrow's date
            $nextDayItem = DailyItemCode::where('user_id', $userId)->whereDate('start_date', $nextDay)->orderBy('start_time', 'asc')->value('item_code'); // Get the first record of the next day

            $nextItemCode = $nextDayItem ?? null; // If exists, assign; else, remain null

            if ($nextItemCode === null) {
                return response()->json(['message' => 'Belum ada item yang diassign']);
            }
        }

        // Create a new mould change log entry
        $mouldChange = MouldChangeLog::create([
            'user_id' => $userId,
            'pic' => $request->pic_name,
            'item_code' => $nextItemCode,
            'created_at' => Carbon::now(), // Start time
        ]);

      

        return response()->json(['message' => 'Mould change started', 'log_id' => $mouldChange->id,'operator' => [
            'name' => $operatorUser->name,
           'profile_path' => $operatorUser->profile_picture 
            ? asset('storage/' . $operatorUser->profile_picture)  // Convert to full URL
            : asset('images/default_profile.jpg'),  // Default profile image
    ],]);
    }



    public function startAdjustMachine(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::now()->format('Y-m-d');

        $request->validate([
            'pic_name' => 'required|string|max:255',
        ]);

        $currentItemCode = MachineJob::where('user_id', $userId)->value('item_code');

        $operatorUser = OperatorUser::where('name',$request->pic_name)->first();
        
        // Get all item_codes for today, ordered by shift or time
        $dailyItems = DailyItemCode::where('user_id', $userId)
            ->whereDate('start_date', $today) // Match today's records
            ->orderBy('start_time', 'asc') // Order by shift timing
            ->pluck('item_code')
            ->toArray(); // Convert to an array for easier processing

     

        // Find the next item_code in sequence
        $nextItemCode = null;
        $currentIndex = array_search($currentItemCode, $dailyItems);

        if ($currentIndex !== false && isset($dailyItems[$currentIndex + 1])) {
            $nextItemCode = $dailyItems[$currentIndex + 1]; // Get the next item
        } else {
            // Special case: Find the first item_code of the next day
            $nextDay = Carbon::tomorrow()->format('Y-m-d'); // Get tomorrow's date
            $nextDayItem = DailyItemCode::where('user_id', $userId)->whereDate('start_date', $nextDay)->orderBy('start_time', 'asc')->value('item_code'); // Get the first record of the next day

            $nextItemCode = $nextDayItem ?? null; // If exists, assign; else, remain null

            if ($nextItemCode === null) {
                return response()->json(['message' => 'Belum ada item yang diassign']);
            }
        }

        // Create a new mould change log entry
        $adjustMachine = AdjustMachineLog::create([
            'user_id' => $userId,
            'pic' => $request->pic_name,
            'item_code' => $nextItemCode,
            'created_at' => Carbon::now(), // Start time
        ]);

        // Set machine job user_id to NULL (machine is inactive)
        MachineJob::where('user_id', $userId)->update(['item_code' => null, 'shift' => null]);

        return response()->json(['message' => 'Adjust Machine started', 'log_id' => $adjustMachine->id,'operator' => [
            'name' => $operatorUser->name,
           'profile_path' => $operatorUser->profile_picture 
            ? asset('storage/' . $operatorUser->profile_picture)  // Convert to full URL
            : asset('images/default_profile.jpg'),  // Default profile image
    ],]);
    }



    public function endMouldChange()
    {
        $userId = Auth::id();

        // Update the last mould change log where user_id matches
        $mouldChange = MouldChangeLog::where('user_id', $userId)
            ->whereNull('end_time') // Find an ongoing mould change
            ->latest()
            ->first();

        if ($mouldChange) {
            $mouldChange->update(['end_time' => Carbon::now()]);

            return response()->json(['message' => 'Mould change completed']);
        }

        return response()->json(['error' => 'No active mould change found'], 400);
    }


    public function endAdjustMachine()
    {
        $userId = Auth::id();

        // Update the last mould change log where user_id matches
        $AdjustMachine = AdjustMachineLog::where('user_id', $userId)
            ->whereNull('end_time') // Find an ongoing mould change
            ->latest()
            ->first();

        if ($AdjustMachine) {
            $AdjustMachine->update(['end_time' => Carbon::now()]);

            return response()->json(['message' => 'Adjust Machine completed']);
        }

        return response()->json(['error' => 'No active mould change found'], 400);
    }


    public function verifyNIKPassword(Request $request)
    {
        $nik = $request->input('nik');
        $password = $request->input('password');

        // Validate the incoming data
        $validated = $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        // Attempt to find the operator user by NIK
        $operatorUser = OperatorUser::where('name', $nik)->first();

        if ($operatorUser && $password === $operatorUser->password) {
            $profilePicture = $operatorUser->profile_picture ? asset('storage/' . $operatorUser->profile_picture) : asset('default-avatar.png');
            // If user exists and password matches, return success
            return response()->json(['success' => true, 'message' => 'NIK and password are verified','profile_picture' => $profilePicture,
            'operator_name' => $operatorUser->name,]);
        }

        // If the NIK or password doesn't match, return an error
        return response()->json(['success' => false, 'message' => 'Invalid NIK or Password'], 400);
    }

    public function verifyNik(Request $request)
    {
        $request->validate([
            'nik' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = OperatorUser::where('name', $request->nik)->first();

        if (!$user || $user->password !== $request->password) { // Direct string check
            return response()->json(['error' => 'Invalid NIK or password'], 401);
        }

        return response()->json([
            'message' => 'NIK Verified Successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name, // Pass the username
                'profile_path' => $user->profile_path, // Include profile picture
            ]
        ]);
    }

    public function autoLogin($user, Request $request)
    {
        // Find the user in the database
        $userData = User::where('name', $user)->first();

        if (!$userData) {
            return redirect('/login')->with('error', 'User not found.');
        }

        // Log in the user
        Auth::login($userData);

        // Redirect to dashboard
        return redirect()->route('dashboard');
    }


}
