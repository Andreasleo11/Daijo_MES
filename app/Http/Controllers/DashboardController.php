<?php

namespace App\Http\Controllers;

use App\Events\ParentDataUpdated;
use App\Models\DailyItemCode;
use Illuminate\Support\Facades\DB;
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
use App\Models\Delivery\SapInventoryFg;
use App\Models\MouldChangeLog;
use App\Models\AdjustMachineLog;
use App\Models\RepairMachineLog;
use App\Models\SpkMaster;
use App\Models\OperatorUser;
use App\Models\User;
use App\Models\HourlyRemark;
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

            $machineJobShift = MachineJob::where('user_id', auth()->user()->id)->first()->shift;
            $machineJobShift = $machineJob->shift ?? 1;

            // dd($machineJobShift);

            // $zone = $user->zone;
            // $pengawasName = $zone?->pengawas;
            // $pengawasUser = $zone?->pengawasUser;
            // $pengawasProfile = $pengawasUser?->profile_picture;

            // $machineJobShift = $machineJob->shift ?? 1;

            $zone = $user->zone;

            $zonePengawas = $zone?->zoneData()
                ->where('shift', $machineJobShift)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->latest('updated_at')
                ->first();

            $pengawasName = $zonePengawas?->pengawas;

            $pengawasUser = \App\Models\OperatorUser::where('name', $pengawasName)->first();

            $pengawasProfile = $pengawasUser?->profile_picture;


            $datas = DailyItemCode::where('user_id', $user->id)
                ->whereDate('schedule_date', Carbon::today())
                ->with('masterItem','scannedData')
                ->get();
        
          
             // Ambil data dari MouldChangeLog sesuai dengan user_id dan tanggal hari ini
            $mouldChangeLogs = MouldChangeLog::where('user_id',  $user->id)
            ->whereDate('created_at', Carbon::today())
            ->get();

            // Ambil data dari AdjustMachineLog sesuai dengan user_id dan tanggal hari ini
            $adjustMachineLogs = AdjustMachineLog::where('user_id', $user->id)
                    ->whereDate('created_at', Carbon::today())
                    ->get();

            // Ambil data dari RepairMachineLog sesuai dengan user_id dan tanggal hari ini
            $repairMachineLogs = RepairMachineLog::where('user_id', $user->id)
                    ->whereDate('created_at', Carbon::today())
                    ->get();

             // Tambahkan array total_pengerjaan di setiap data, melewati yang null
                $mouldChangeLogs->each(function ($log) {
                    // Pastikan created_at dan end_time tidak null
                    if ($log->created_at && $log->end_time) {
                        $createdAt = Carbon::parse($log->created_at);
                        $endTime = Carbon::parse($log->end_time);
                        $log->total_pengerjaan = $endTime->diffInMinutes($createdAt); // Hitung selisih waktu dalam menit
                    } else {
                        $log->total_pengerjaan = null; // Jika null, beri nilai null
                    }
                });

                // Tangani AdjustMachineLogs
                $adjustMachineLogs->each(function ($log) {
                    // Pastikan created_at dan end_time tidak null
                    if ($log->created_at && $log->end_time) {
                        $createdAt = Carbon::parse($log->created_at);
                        $endTime = Carbon::parse($log->end_time);
                         $log->total_pengerjaan = $endTime->diffInMinutes($createdAt); // Hitung selisih waktu dalam menit
                    } else {
                        $log->total_pengerjaan = null; // Jika null, beri nilai null
                    }
                });

                // Tangani RepairMachineLogs
                $repairMachineLogs->each(function ($log) {
                    // Pastikan created_at dan finish_repair tidak null
                    if ($log->created_at && $log->finish_repair) {
                        $createdAt = Carbon::parse($log->created_at);
                        $endTime = Carbon::parse($log->finish_repair);
                        $log->total_pengerjaan = $endTime->diffInMinutes($createdAt); // Hitung selisih waktu dalam menit
                    } else {
                        $log->total_pengerjaan = null; // Jika null, beri nilai null
                    }
                });
            



           // Awal proses
            $itemCollections = [];
            $totalQuantities = [];
            $files = [];
            $itemCode = $user->jobs->item_code ?? null;
       
            // Helper untuk menentukan key utama (gabung ke yang lebih kecil atau yang muncul pertama)
            function getMainItemCode($itemCode, $pairCode) {
                return $itemCode ?? $pairCode;
            }

            // Kumpulkan total quantity dan file berdasarkan item utama
            foreach ($datas as $data) {
                $itemCodeAll = $data->item_code;
                $pairCode = $data->masterItem->pair ?? null;

                // Tentukan item utama
                $mainItemCode = getMainItemCode($itemCodeAll, $pairCode);
                
                // Total quantity
                if (!isset($totalQuantities[$mainItemCode])) {
                    $totalQuantities[$mainItemCode] = 0;
                }
                $totalQuantities[$mainItemCode] += $data->quantity;

                // Simpan file
                $fileData = File::where('item_code', $itemCodeAll)->get();
                $files[$mainItemCode] = $fileData->isEmpty() ? collect() : $fileData;
                // dd($files[$mainItemCode]);
                \Illuminate\Support\Facades\Log::info("files" . $fileData);
                \Illuminate\Support\Facades\Log::info("files" . $files[$mainItemCode]);
                // dd($fileData); ini aku dd muncul mon coba di viewnyua deh , kauanya kalol pair ke
                // Jika ada pair, ambil juga file-nya
                // if ($pairCode) {
                //     $fileDataPair = File::where('item_code', $pairCode)->get(); // ini
                //     dd($fileDataPair);
                //     $files[$mainItemCode] = $fileDataPair->isEmpty() ? collect() : $fileDataPair;
                // }
            }

            // Fungsi bantu untuk alokasi SPK berdasarkan item_code
            function allocateSPKsForItem($itemCode, $totalQty, &$itemCollections, $mainItemCode)
            {
                
                $datas = SpkMaster::where('item_code', $itemCode)
                   // atau 'created_at', tergantung nama kolomnya
                    ->get();
            
                $masterItem = MasterListItem::where('item_code', $itemCode)->first();
                $perpack = $masterItem->standart_packaging_list ?? 1;

                $labelstart = 0;

                foreach ($datas as $spk) {
                    $available_quantity = $spk->planned_quantity - $spk->completed_quantity;

                    if ($totalQty <= 0) break;

                    if ($totalQty <= $available_quantity) {
                        $available_quantity = $totalQty;
                    }

                    $labelstart = ($spk->completed_quantity === 0) ? 0 : ceil($spk->completed_quantity / $perpack);

                    while ($available_quantity > 0) {
                        $labelstart++;
                        $pack_quantity = min($perpack, $available_quantity);
                        $key = $spk->spk_number . '|' . $spk->item_code;

                        if (!isset($itemCollections[$mainItemCode][$key])) {
                            $itemCollections[$mainItemCode][$key] = [
                                'spk' => $spk->spk_number,
                                'item_code' => $spk->item_code,
                                'item_perpack' => $perpack,
                                'available_quantity' => 0,
                                'count' => 0,
                                'start_label' => $labelstart,
                                'end_label' => $labelstart,
                                'scannedData' => 0,
                            ];
                        }

                        $itemCollections[$mainItemCode][$key]['count']++;
                        $itemCollections[$mainItemCode][$key]['end_label'] = $labelstart;
                        $itemCollections[$mainItemCode][$key]['available_quantity'] += $pack_quantity;

                        $available_quantity -= $pack_quantity;
                        $totalQty -= $pack_quantity;
                    }
                }
            }

            // Alokasikan SPK untuk semua item_code, pakai key utama (gabungan)
            foreach ($datas as $data) {
                $itemCodeAll = $data->item_code;
                $pairCode = $data->masterItem->pair ?? null;

                $mainItemCode = getMainItemCode($itemCodeAll, $pairCode);

                // SPK untuk item utama
                allocateSPKsForItem($itemCodeAll, $data->quantity, $itemCollections, $mainItemCode);

                // SPK untuk pair-nya jika ada (juga masuk ke key utama!)
                if ($pairCode) {
                    allocateSPKsForItem($pairCode, $data->quantity, $itemCollections, $mainItemCode);
                }
            }

            // Ambil data scanned dan total quantity per SPK
            foreach ($itemCollections as $mainItemCode => &$spkList) {
                foreach ($spkList as &$spkData) {
                    $spkData['scannedData'] = ProductionScannedData::where('spk_code', $spkData['spk'])
                        ->where('item_code', $spkData['item_code'])
                        ->count();

                    $spkData['totalquantity'] = ProductionScannedData::where('spk_code', $spkData['spk'])
                        ->where('item_code', $spkData['item_code'])
                        ->sum('quantity');
                }
            }


            $hourlyRemarksActiveDIC = null;
            $activeDIC = DailyItemCode::where('user_id', $user->id)
                ->whereDate('schedule_date', Carbon::today())
                ->where('item_code', $itemCode)
                ->with('masterItem','scannedData','masterFg','hourlyRemarks')
                ->whereNull('is_done')
                ->first();
            if (!$activeDIC) {
                    // Kalau null, cari lagi tanpa filter tanggal
                $activeDIC = DailyItemCode::where('user_id', $user->id)
                    ->where('item_code', $itemCode)
                    ->with(['masterItem','scannedData','masterFg','hourlyRemarks'])
                    ->whereNull('is_done')
                    ->first();
                // dd($activeDIC);
            }

            if ($activeDIC) {
                $totalScannedQuantity = $activeDIC->scannedData->sum('quantity');
                $scannedCount = $activeDIC->scannedData->count();
                $hourlyRemarksActiveDIC = HourlyRemark::where('dic_id', $activeDIC->id)
                    ->orderBy('start_time')
                    ->get();
                // dd($hourlyRemarksActiveDIC);
            } else {
                $totalScannedQuantity = 0;
                $scannedCount = 0;
            }
            // dd($activeDIC);
            $activeID = $activeDIC?->id;

            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();
            $userId = auth()->id();

            $hourlyRemarks = HourlyRemark::whereHas('dailyItemCode', function ($query) use ($today, $tomorrow, $userId) {
                    $query->where(function ($q) use ($today, $tomorrow) {
                        $q->whereDate('schedule_date', $today)
                        ->orWhereDate('schedule_date', $tomorrow);
                    })
                    ->where('user_id', $userId); // tambahkan ini untuk memfilter per user
                })
                ->where(function ($q) {
                    $q->whereBetween('start_time', ['07:30:00', '23:59:59'])
                    ->orWhereBetween('start_time', ['00:00:00', '07:30:00']);
                })
                ->orderBy('start_time')
                ->get();
       
            $spkData = ProductionScannedData::where('dic_id', $activeID)
                ->get();
            // dd($spkData);
            // dd($hourlyRemarks);
            // dd($activeDIC);
            

            return view('dashboards.dashboard-operator', compact('files', 'datas', 'itemCode', 'uniquedata', 'machineJobShift', 'dataWithSpkNo', 'machinejobid', 'itemCollections',  'mouldChangeLogs', 'adjustMachineLogs', 'repairMachineLogs','zone','pengawasName','pengawasProfile', 'activeDIC', 'totalScannedQuantity', 'scannedCount', 'hourlyRemarksActiveDIC', 'hourlyRemarks','spkData'));
        } elseif ($user->role->name === 'WORKSHOP') {
            return view('dashboards.dashboard-workshop', compact('user'));
        } else {
            return view('dashboard', compact('user'));
        }
    }

    public function updateRemark(Request $request, $id)
    {
        $request->validate([
            'remark' => 'nullable|string|max:255',
        ]);

        $remark = HourlyRemark::findOrFail($id);
        $remark->remark = $request->remark;
        $remark->save();

        return response()->json(['success' => true]);
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
        $verified_data = DailyItemCode::where('user_id', $user->id)->whereNull('is_done')->get();
        
        // Check if the item code exists for the user
        $itemCodeExists = $verified_data->contains('item_code', $itemCode);

        if ($itemCodeExists) {
            // Retrieve the specific DailyItemCode for the item code
            $dailyItemCode = DailyItemCode::where('item_code', $itemCode)->whereNull('is_done')->first();
            
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
            $datas = SpkMaster::where('item_code', $item_code) // atau 'created_at', tergantung nama kolomnya
                ->get();

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
                $qrCodeData = implode("\t", [$labelData['spk'], $labelData['quantity'], $labelData['warehouse'], $labelData['label']]);
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
        $datas = json_decode($request->input('datas'), true);
        $uniquedata = json_decode($request->input('uniqueData'));
        $activeDIC = json_decode($request->input('activedic'));
        

        $spk_code = $request->input('spk_code_auto');
        $quantity = $request->input('quantity_auto');
        $warehouse = $request->input('warehouse_auto');
        $label = $request->input('label_auto');
        $user = $request->input('nik') ?? session('verifiedNIK');
        $now = Carbon::now('Asia/Jakarta');

        // ✅ Perbaikan logika slot waktu
        if ($now->lt(Carbon::createFromTime(7, 30, 0, 'Asia/Jakarta'))) {
            $base = Carbon::yesterday('Asia/Jakarta')->setTime(7, 30, 0);
        } else {
            $base = Carbon::today('Asia/Jakarta')->setTime(7, 30, 0);
        }

        $diffMinutes = $base->diffInMinutes($now);
        $slotIndex = floor($diffMinutes / 60);
        $startTime = $base->copy()->addMinutes($slotIndex * 60)->format('H:i:s');
        $endTime = $base->copy()->addMinutes(($slotIndex + 1) * 60)->format('H:i:s');

        $cycletime = $activeDIC->master_fg->cycle_time * 60;
        $target = ceil(3600 / $cycletime);

        if (
            !empty($activeDIC->master_item?->pair) || 
            !empty($activeDIC->master_fg?->pair)
        ) {
            $target *= 2;
        }


        // Validasi dasar inputan
        $request->validate([
            'spk_code_auto' => 'required|string',
            'warehouse_auto' => 'required|string',
            'quantity_auto' => 'required|integer',
            'label_auto' => 'required|string',
        ]);

        $dicId = $activeDIC->id;

        $hourlyRemark = HourlyRemark::where('dic_id', $dicId)
            ->where('start_time', $startTime)
            ->where('end_time', $endTime)
            ->first();

        $existingScan = ProductionScannedData::where('spk_code', $spk_code)
            ->where('label', $label)
            ->first();

        if ($existingScan) {
            return redirect()->back()->withErrors(['error' => 'Label ini sudah pernah discan sebelumnya.']);
        }

        $trueItemcode = SpkMaster::where('spk_number', $spk_code)->first()?->item_code ?? $activeDIC->item_code;

        ProductionScannedData::create([
            'spk_code' => $spk_code,
            'dic_id' => $dicId,
            'item_code' => $trueItemcode,
            'quantity' => $quantity,
            'warehouse' => $warehouse,
            'label' => $label,
            'user' => $user,
        ]);

        $totalActual = ProductionScannedData::where('dic_id', $dicId)
            ->whereRaw("TIME(CONVERT_TZ(created_at, '+00:00', '+07:00')) >= ?", [$startTime])
            ->whereRaw("TIME(CONVERT_TZ(created_at, '+00:00', '+07:00')) < ?", [$endTime])
            ->sum('quantity');

        $isAchieve = $totalActual >= $target ? 1 : 0;

        if ($hourlyRemark) {
            $hourlyRemark->update([
                'actual' => $totalActual,
                'is_achieve' => $isAchieve,
                'updated_at' => now(),
            ]);
        } else {
            HourlyRemark::create([
                'dic_id' => $dicId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'target' => $target,
                'actual' => $totalActual,
                'is_achieve' => $isAchieve,
                'pic' => $user,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('dashboard')->with('deactivateScanMode', false);
    }

    public function submitSPK(Request $request)
    {
        $all = $request->all(); // atau dd($request->all());
        $datas = json_decode($request->input('datas'), true);
        $uniquedata = json_decode($request->input('uniqueData'));
        $dic = json_decode($request->input('activedic'));
        $dicId = $dic->id;

        $spkToUpdate = ProductionScannedData::where('dic_id', $dicId)
            ->get();
        // dd($spkToUpdate);
        // Loop berdasarkan item_code
        foreach ($spkToUpdate as $scanned) {
            // spk_code dan quantity dari ProductionScannedData
            $spkNumber = $scanned->spk_code; // asumsi 'spk_code' ada di tabel ini
            $additionalQty = (int) $scanned->quantity;

            if ($additionalQty > 0) {
                $spk = SpkMaster::where('spk_number', $spkNumber)->first();
                if ($spk) {
                    $spk->completed_quantity += $additionalQty;
                    $spk->save();
                }
            }
        }

        // Update status DIC jadi done
        DailyItemCode::where('id', $dicId)->update(['is_done' => 1]);

        return redirect()->back()->with('success', 'SPK quantities updated successfully.');
    }


    public function procesProductionBarcodesLoss(Request $request)
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
          $user = $request->input('nik') ?? session('verifiedNIK'); // fallback ke session jika tidak dikirim
       
     
    
  
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
            'spk_code' => 'string',
            'warehouse' => 'string',
            'quantity' => 'integer',
            'label' => 'string',
        ]);


        // Validation logic for SPK code and label ranges
        $validator = Validator::make($request->all(), [
            'spk_code' => 'string',
            'warehouse' => 'string',
            'quantity' => 'integer',
            'label' => 'string',
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

    // public function resetJob()
    // {
     
    //     $currentTime = now(); // For current time, or you can use Carbon::parse('specific-time')
    //     // Define shift times
    //     $shift1Start = Carbon::parse('07:30:00');
    //     $shift1End = Carbon::parse('15:30:00');
    //     $shift2Start = Carbon::parse('15:31:00');
    //     $shift2End = Carbon::parse('23:30:00');
    //     $shift3Start = Carbon::parse('23:31:00');
    //     $shift3End = Carbon::parse('07:29:59');

    //     // Determine the shift based on the current time
    //     $shift = null;

    //     if ($currentTime->between($shift1Start, $shift1End)) {
    //         $shift = 1;
    //     } elseif ($currentTime->between($shift2Start, $shift2End)) {
    //         $shift = 2;
    //     } elseif ($currentTime->between($shift3Start, $shift3End) || $currentTime->lessThan($shift1Start)) {
    //         $shift = 3;
    //     }
       
    //     // Reset the machine job
    //     MachineJob::where('user_id', auth()->user()->id)->update([
    //         'item_code' => null,
    //         'shift' => $shift,
    //     ]);

    //     return redirect()->back()->with('success', 'Job has been resetted!');
    // }

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
            ->whereDate('schedule_date', $today) // Match today's records
            ->orderBy('start_time', 'asc') // Order by shift timing
            ->pluck('item_code')
            ->toArray(); // Convert to an array for easier processing
        
     

        // Find the next item_code in sequence
        $nextItemCode = null;
        $currentIndex = array_search($currentItemCode, $dailyItems);

        if ($currentIndex !== false && isset($dailyItems[$currentIndex + 1])) {
            $nextItemCode = $dailyItems[$currentIndex + 1]; // Get the next item
        }
        else if($currentIndex === false) 
        {
        $nextItemCode = $dailyItems[1]; 
        }
        else {
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
        }
        else if($currentIndex === false) 
        {
        $nextItemCode = $dailyItems[1]; 
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


    public function startRepairMachine(Request $request)
    {
        $userId = Auth::id();
        $today = Carbon::now()->format('Y-m-d');

        $request->validate([
            'pic_name' => 'required|string|max:255',
        ]);


        $operatorUser = OperatorUser::where('name',$request->pic_name)->first();

        // Create a new mould change log entry
        $repairmachine = RepairMachineLog::create([
            'user_id' => $userId,
            'pic' => $request->pic_name,
            'created_at' => Carbon::now(), // Start time
        ]);

      

        return response()->json(['message' => 'Repair Machine started', 'repair_id' => $repairmachine->id,'operator' => [
            'name' => $operatorUser->name,
           'profile_path' => $operatorUser->profile_picture 
            ? asset('storage/' . $operatorUser->profile_picture)  // Convert to full URL
            : asset('images/default_profile.jpg'),  // Default profile image
    ],]);
    }



    public function endMouldChange(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        // Update the last mould change log where user_id matches
        $mouldChange = MouldChangeLog::where('user_id', $userId)
            ->whereNull('end_time') // Find an ongoing mould change
            ->latest()
            ->first();

        if ($mouldChange) {
            $mouldChange->update(['end_time' => Carbon::now(),
        'remark' => $request->remarks,]);

            return response()->json(['message' => 'Mould change completed']);
        }

        return response()->json(['error' => 'No active mould change found'], 400);
    }

    private function resetUserJob($userId)
    {
        $currentTime = now();

        $shift1Start = Carbon::parse('07:30:00');
        $shift1End = Carbon::parse('15:30:00');
        $shift2Start = Carbon::parse('15:31:00');
        $shift2End = Carbon::parse('23:30:00');
        $shift3Start = Carbon::parse('23:31:00');
        $shift3End = Carbon::parse('07:29:59');

        $shift = null;
        if ($currentTime->between($shift1Start, $shift1End)) {
            $shift = 1;
        } elseif ($currentTime->between($shift2Start, $shift2End)) {
            $shift = 2;
        } elseif ($currentTime->between($shift3Start, $shift3End) || $currentTime->lessThan($shift1Start)) {
            $shift = 3;
        }

        MachineJob::where('user_id', $userId)->update([
            'item_code' => null,
            'shift' => $shift,
        ]);
    }

    public function resetJob()
    {
        $this->resetUserJob(auth()->id());
        return redirect()->back()->with('success', 'Job has been resetted!');
    }


    public function endAdjustMachine(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        // Update the last mould change log where user_id matches
        $AdjustMachine = AdjustMachineLog::where('user_id', $userId)
            ->whereNull('end_time') // Find an ongoing mould change
            ->latest()
            ->first();

        if ($AdjustMachine) {
            $AdjustMachine->update(['end_time' => Carbon::now(),
            'remark' => $request->remarks,]);

            // Reset machine job langsung di sini
            $this->resetUserJob($userId);


            return response()->json(['message' => 'Adjust Machine completed']);
        }

        return response()->json(['error' => 'No active mould change found'], 400);
    }
    

    public function endRepairMachine(Request $request)
    {
        $request->validate([
            'problem' => 'required|string|max:255',
            'remarks' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();

        $repairLog = RepairMachineLog::where('user_id', $userId)
            ->whereNull('finish_repair')
            ->latest()
            ->first();

        if ($repairLog) {
            $repairLog->update([
                'finish_repair' => Carbon::now(),
                'problem' => $request->problem,
                'remark' => $request->remarks,
            ]);

            return response()->json(['message' => 'Repair Machine completed']);
        }

        return response()->json(['error' => 'No active repair process found'], 400);
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

    public function deleteScanData($id)
    {
        $scan = ProductionScannedData::find($id);
        if (!$scan) {
            return back()->with('error', 'Data scan tidak ditemukan');
        }

        DB::beginTransaction();

        try {
            // Ambil informasi dasar
            $dicId = $scan->dic_id;
            $scanTime = Carbon::parse($scan->created_at)->timezone('Asia/Jakarta');
            $scanHour = $scanTime->format('H:i:s');
            $userId = $scan->user;
        

            // Cari entri hourly yang cocok
            $hourly = HourlyRemark::where('dic_id', $dicId)
                ->where('pic', $userId)
                ->where('start_time', '<=',  $scanHour)
                ->where('end_time', '>',  $scanHour)
                ->first();
            
            if ($hourly) {
                $hourly->actual = max(0, $hourly->actual - $scan->quantity); // hindari negatif
                // Update is_achieve jika perlu
                if ($hourly->actual < $hourly->target) {
                    $hourly->is_achieve = 0;
                }
                $hourly->save();
            }

            $scan->delete();

            DB::commit();

            return back()->with('success', 'Scan berhasil dihapus dan actual diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }


}
