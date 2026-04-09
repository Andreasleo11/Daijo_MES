<?php

namespace App\Http\Controllers\Store;
 
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\ScannedData;
use App\Models\SoData;
use App\Models\UpdateLog;
use App\Models\SpkItemHistory;
use App\Models\MasterItemPhoto;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SoImport;
use App\Exports\SoDataExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Shared\Date;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SOController extends Controller
{
    public function index(Request $request)
    {
        $isDone = $request->query('is_done', 'all');
        $docNum = $request->query('doc_num', '');
    
        // Build the query based on filter
        $query = SoData::select('doc_num', 'is_done', 'create_date')->distinct();
    
        if (!empty($docNum)) {
            $query->where('doc_num', $docNum);
        }
    
        // Apply filter for `is_done` if specified
        if ($isDone !== 'all') {
            $query->where('is_done', $isDone);
        }
    
        // Paginate the results
        $docNums = $query->orderBy('doc_num', 'desc')->paginate(10);
    
        return view('store.soindex', compact('docNums'));
    }

    public function indexpegawai()
    {
        return view('store.sopegawaiindex');
    }

    public function process($docNum)
    {
        // 1. Ambil data mentah dari DB (termasuk is_done)
        $rawItems = SoData::where('doc_num', $docNum)->get();
        if ($rawItems->isEmpty()) {
            return view('store.soresults', [
                'data' => collect(),
                'docNum' => $docNum,
                'allFinished' => false,
                'allDone' => false
            ]);
        }

        // Cek status is_done dari seluruh dokumen
        $allDone = $rawItems->every('is_done', 1);
        $date = $rawItems->first()->posting_date;
        $customer = $rawItems->first()->customer;

        // --- OPTIMASI: Ambil semua hitungan scan sekaligus (Single Query) ---
        $scanSummaries = ScannedData::where('doc_num', $docNum)
            ->select('item_code', DB::raw('count(*) as ctn_count'), DB::raw('sum(quantity) as total_qty'))
            ->groupBy('item_code')
            ->get()
            ->keyBy('item_code');

        // 2. Evaluasi status finish tiap item berdasarkan jumlah scan
        $data = $rawItems->groupBy('item_code')
            ->map(function ($group) use ($docNum, $scanSummaries) {
                $itemCode = $group->first()->item_code;
                $totalQuantity = $group->sum('quantity');
                $entry = $group->first();
                $entry->quantity = $totalQuantity;

                // Ambil data dari summary yang sudah di-pre-fetch
                $summary = $scanSummaries->get($itemCode);
                $scannedCount = $summary ? $summary->ctn_count : 0;
                $scannedTotalQty = $summary ? $summary->total_qty : 0;
                
                $entry->scannedCount = $scannedCount;
                $entry->scannedTotalQuantity = $scannedTotalQty; // Field baru untuk dipakai di view
                $packagingQuantity = $entry->packaging_quantity;
               
                // Logika penentuan item selesai (is_finish)
                $requiredBoxes = ($packagingQuantity > 0) ? (int)ceil($totalQuantity / $packagingQuantity) : 0;
                $statusFinish = ($scannedCount >= $requiredBoxes && $requiredBoxes > 0) ? 1 : 0;
                
                // Hanya set jika berbeda agar tidak membebani memori/DB nanti
                $entry->is_finish = $statusFinish;

                return $entry;
            })
            ->values();

        // 3. Update status item ke DB secara spesifik (Hanya jika perlu perbaikan status)
        foreach ($data as $entry) {
            SoData::where('doc_num', $docNum)
                ->where('item_code', $entry->item_code)
                ->where('is_finish', '!=', $entry->is_finish) // Optimasi: update hanya jika berubah
                ->update(['is_finish' => $entry->is_finish]);
        }

        // 4. Kalkulasi Akhir untuk tampilan tombol
        $allFinished = $data->every('is_finish', 1);

        // Ambil data scan detail untuk tabel bawah (sudah include di scanSummaries tapi ini untuk log per baris)
        $scandatas = ScannedData::where('doc_num', $docNum)
            ->orderBy('label')
            ->get()
            ->groupBy('item_code');

        return view('store.soresults', compact('data', 'docNum', 'date', 'customer', 'scandatas', 'allFinished', 'allDone'));
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'spk_code' => 'required|string',
            'quantity' => 'required|integer',
            'warehouse' => 'required|string',
            'label' => 'required|integer',
        ]);

        $doc_num = $request->input('so_number');
        $spk_code = $request->input('spk_code');
        $quantity = $request->input('quantity');
        $warehouse = $request->input('warehouse');
        $label = $request->input('label');
        



        $item_code = SpkItemHistory::where('spk_number', $spk_code)
        ->value('item_code');
        

        // Fetch the item data
        $item = SoData::where('item_code', $item_code)->where('doc_num', $doc_num)->first();
        
        if (! $item) {
            $msg = 'Item not found';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->withErrors(['error' => $msg]);
        }

        $existingScans = ScannedData::where('item_code', $item_code)
            ->where('doc_num', $doc_num)
            ->get();

        $scannedTotalQuantity = $existingScans->sum('quantity') + $quantity;
       
        if ($scannedTotalQuantity > $item->quantity) {
            $msg = 'All required CTN have been scanned / Quantity Tidak benar';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->withErrors(['error' => $msg]);
        }

        $photo = MasterItemPhoto::where('item_code', $item_code)->first();
        // Check if the scanned data already exists
        $existingScan = ScannedData::where('item_code', $item_code)
            ->where('label', $label)
            ->where('doc_num', $doc_num)
            ->first();

        if ($existingScan) {
            $msg = 'Data already scanned';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->withErrors(['error' => $msg]);
        }

        // Add new scanned data
        $newScan = ScannedData::create([
            'doc_num' => $doc_num,
            'item_code' => $item_code,
            'quantity' => $quantity,
            'warehouse' => $warehouse,
            'label' => $label,
        ]);

        // --- Tambahan LOGIKA UNTUK AJAX RESPONSES ---
        if ($request->ajax() || $request->wantsJson()) {
            // Kalkulasi data terbaru
            $scannedCount = ScannedData::where('doc_num', $doc_num)->where('item_code', $item_code)->count();
            $scannedTotalQty = ScannedData::where('doc_num', $doc_num)->where('item_code', $item_code)->sum('quantity');

            // Cek apakah seluruh SO sudah selesai
            $rawItems = SoData::where('doc_num', $doc_num)->get();
            $scanSummaries = ScannedData::where('doc_num', $doc_num)
                ->select('item_code', DB::raw('count(*) as count'))
                ->groupBy('item_code')
                ->pluck('count', 'item_code');

            $allFinished = $rawItems->groupBy('item_code')->every(function($group) use ($scanSummaries) {
                $item = $group->first();
                $totalQty = $group->sum('quantity');
                $reqBoxes = $item->packaging_quantity > 0 ? (int)ceil($totalQty / $item->packaging_quantity) : 0;
                $currentBoxes = $scanSummaries->get($item->item_code, 0);
                return ($currentBoxes >= $reqBoxes && $reqBoxes > 0);
            });

            return response()->json([
                'success' => true,
                'message' => 'Barcode scanned successfully',
                'photo'   => $photo ? asset('storage/' . $photo->photo_path) : null,
                'item_code' => $item_code,
                'scannedCount' => $scannedCount,
                'scannedTotalQuantity' => $scannedTotalQty,
                'allFinished' => $allFinished,
                'newScan' => [
                    'id' => $newScan->id,
                    'quantity' => $newScan->quantity,
                    'warehouse' => $newScan->warehouse,
                    'label' => $newScan->label,
                    'created_at' => $newScan->created_at->format('Y-m-d H:i:s')
                ]
            ]);
        }

        return redirect()->back()->with([
            'success' => 'Barcode scanned successfully',
            'photo'   => $photo ? $photo->photo_path : null,
        ]);
    }

    public function updateSoData($docNum)
    {
        // Update all SoData records with the given docNum
        SoData::where('doc_num', $docNum)
            ->update(['is_done' => true]);

        // Redirect back with a success message or to another route
        return redirect()->route('pegawai.scan')->with('status', 'All records updated successfully.');
    }

    public function import(Request $request)
    {
        set_time_limit(3000);       
        // dd($request->file('import_file'));

        // $file = $request->file('import_file')->store('temp'); // Store temporarily
        
        // $filePath = $uploadedFile->getRealPath();

            // Store the uploaded file temporarily
        $uploadedFile = $request->file('import_file')->store('temp');
        $filePath = storage_path('app/' . $uploadedFile);

        // Read the Excel file and process the data
        $data = Excel::toArray([], $filePath)[0];
        
        // Remove the first row (header)
        array_shift($data);
    
        $uniqueRows = [];
       
        // Loop through the data to check for duplicates
        foreach ($data as &$row) {
            array_shift($row);
            // Format the date in column 3 (index 2) to yyyy-mm-dd
            if (isset($row[2]) && !empty($row[2])) {
                $row[2] = \Carbon\Carbon::createFromFormat('d/m/Y', $row[2])->format('Y-m-d');
            }

            // Convert the number in column 6 (index 5) to integer (without decimal places)
            if (isset($row[5])) {
                // Remove any commas and decimals, then convert to integer
                $deliveryQuantity = str_replace(',', '', $row[5]); // Remove commas
                $deliveryQuantity = (int)number_format((float)$deliveryQuantity, 0, '.', ''); // Convert to integer
                $row[5] = $deliveryQuantity; // Update the row
            } else {
                $row[5] = 0; // Set to a default value if it's not a number
            }

            // Convert the number in column 8 (index 7) to integer (without decimal places)
            if (isset($row[7])) {
                // Remove any commas and decimals, then convert to integer
                $packagingQuantity = str_replace(',', '', $row[7]); // Remove commas
                $packagingQuantity = (int)number_format((float)$packagingQuantity, 0, '.', ''); // Convert to integer
                $row[7] = $packagingQuantity; // Update the row
            } else {
                $row[7] = 0; // Set to a default value if it's not a number
            }

            if (isset($row[9]) && !empty($row[9])) {
                $row[9] = \Carbon\Carbon::createFromFormat('d/m/Y', $row[9])->format('Y-m-d');
            }

            if (isset($row[10]) && !empty($row[10])) {
                $row[10] = \Carbon\Carbon::createFromFormat('d/m/Y', $row[10])->format('Y-m-d');
            }
           
            // Create a unique key based on columns 1 and 4 (doc_num and item_code)
            $uniqueKey = $row[0] . '-' . $row[3]; 

            // Check if this unique key already exists in the $uniqueRows array
            if (isset($uniqueRows[$uniqueKey])) {
                // If a duplicate is found, sum columns 6 and 8
                $uniqueRows[$uniqueKey][5] += $row[5]; // Sum the quantities
                // $uniqueRows[$uniqueKey][8] += $row[8];  Sum the packaging quantities
            } else {
                // If not a duplicate, store the row in the $uniqueRows array
                $uniqueRows[$uniqueKey] = $row;
            }
        }
        

        // After processing, $uniqueRows will contain only unique rows with summed quantities and packaging quantities
        $data = array_values($uniqueRows);
        
        // Store the processed file data into a CSV
        $excelFileName = 'sodata.csv';
        $excelFilePath = 'public/' . $excelFileName;

        $import = new SoImport();

        // Delete old data where is_finish and is_done are 0
        $import->deleteOldData();

        Excel::store(new SoDataExport($data), 'public/' . $excelFileName);
        // Import the processed file into the database
        Excel::import(new SoImport, storage_path('app/' . $excelFilePath));

        UpdateLog::updateOrCreate(
            [], // Empty array to match all records
            ['last_upload_time' => now()] // Set the current time
        );
    
        // Flash a success message to the session
        session()->flash('success', 'Excel file processed and imported successfully.');

        session(['last_upload_time' => now()]);

        // Redirect to the SO index route
        return redirect()->route('so.index');
    }

     public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $scan = ScannedData::findOrFail($id);
        $scan->quantity = $request->quantity;
        $scan->save();

        return back()->with('success', 'Quantity updated successfully');
    }

    public function destroy($id)
    {
        $scan = ScannedData::findOrFail($id);
        $scan->delete();

        return back()->with('success', 'Data deleted successfully');
    }

    public function storeFromSap(Request $request)
    {
        if (!is_array($request->all())) {
            return response()->json([
                'message' => 'Invalid payload format'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->all() as $row) {

                $validator = Validator::make($row, [
                    'doc_num' => 'required|string',
                    'customer' => 'required|string',
                    'posting_date' => 'required|date',
                    'item_code' => 'required|string',
                    'item_name' => 'required|string',
                    'quantity' => 'required|integer',
                    'sales_uom' => 'nullable|string',
                    'packaging_quantity' => 'required|integer',
                    'sales_pack' => 'nullable|string',
                    'create_date' => 'required|date',
                    'update_date' => 'required|date',
                    'update_fulltime' => 'required|integer',
                ]);

                if ($validator->fails()) {
                    throw new \Exception($validator->errors()->first());
                }

                // Cari record, atau buat baru
                $soData = SoData::firstOrNew([
                    'doc_num' => $row['doc_num'],
                    'item_code' => $row['item_code'],
                ]);

                // Set data dari SAP
                $soData->customer = $row['customer'];
                $soData->posting_date = $row['posting_date'];
                $soData->item_name = $row['item_name'];
                $soData->quantity = $row['quantity'];
                $soData->sales_uom = $row['sales_uom'] ?? null;
                $soData->packaging_quantity = $row['packaging_quantity'];
                $soData->sales_pack = $row['sales_pack'] ?? null;
                $soData->create_date = $row['create_date'];
                $soData->update_date = $row['update_date'];
                $soData->update_fulltime = $row['update_fulltime'];

                // Hanya set 0 saat insert baru
                if (!$soData->exists) {
                    $soData->is_finish = 0;
                    $soData->is_done = 0;
                }

                $soData->save();
            }

            DB::commit();

            return response()->json([
                'message' => 'DO data processed successfully',
                'total' => count($request->all())
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to process data',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function storeSpkNew(Request $request)
    {
        if (!is_array($request->all())) {
            return response()->json([
                'message' => 'Invalid payload format'
            ], 422);
        }

        DB::beginTransaction();

        try {
            foreach ($request->all() as $row) {

                $validator = Validator::make($row, [
                    'spk_number' => 'required|string',
                    'item_code'       => 'required|string',
                ]);

                if ($validator->fails()) {
                    throw new \Exception($validator->errors()->first());
                }

                SpkItemHistory::create([
                    'spk_number'      => $row['spk_number'],
                    'item_code'       => $row['item_code'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'SPK data inserted successfully',
                'total'   => count($request->all())
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to insert SPK data'
            ], 500);
        }
    }

    public function dashboard(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());
        $date = Carbon::parse($selectedDate);
        
        // --- WEEKLY HIGHLIGHTS ---
        $startOfWeek = Carbon::now()->startOfWeek(); 
        $endOfWeek = Carbon::now()->endOfWeek();

        $weeklyScans = ScannedData::whereBetween('created_at', [$startOfWeek, $endOfWeek])->get();
        $weeklyTotalScans = $weeklyScans->count();
        $weeklyActiveDocsCount = $weeklyScans->pluck('doc_num')->unique()->count();
        
        // Hitung SO yang selesai minggu ini (status is_finish=1)
        $weeklyFinishedItems = SoData::where('is_finish', 1)
            ->whereBetween('update_date', [$startOfWeek, $endOfWeek])
            ->count();

        // --- DAILY VIEW ---
        $dayStart = $date->copy()->startOfDay();
        $dayEnd = $date->copy()->endOfDay();

        $selectedDateScans = ScannedData::whereBetween('created_at', [$dayStart, $dayEnd])
            ->with('soData')
            ->orderBy('created_at', 'desc')
            ->get();

        // Identifikasi unik DocNum yang ada aktivitas scanning pada TANGGAL TERPILIH
        $activeDocNums = $selectedDateScans->pluck('doc_num')->unique();

        // Hitung progress untuk setiap DocNum aktif pada tanggal tersebut
        $soProgress = [];
        foreach ($activeDocNums as $docNum) {
            $items = SoData::where('doc_num', $docNum)->get();
            if ($items->isEmpty()) continue;

            $totalItems = $items->count();
            
            // Hitung total label yang seharusnya di-scan untuk seluruh SO ini
            $totalLabelsRequired = $items->sum(function($item) {
                return $item->packaging_quantity > 0 ? (int)ceil($item->quantity / $item->packaging_quantity) : 0;
            });

            // Hitung total label yang sudah benar-benar di-scan (akumulatif s/d sekarang)
            $totalLabelsScanned = ScannedData::where('doc_num', $docNum)->count();

            // Persentase progress
            $progressPercent = $totalLabelsRequired > 0 
                ? round(($totalLabelsScanned / $totalLabelsRequired) * 100, 1) 
                : 0;

            // Jumlah item yang statusnya sudah 'is_finish'
            $finishedItemsCount = $items->where('is_finish', 1)->count();

            $soProgress[$docNum] = [
                'doc_num' => $docNum,
                'customer' => $items->first()->customer ?? 'No Customer',
                'total_items' => $totalItems,
                'finished_items' => $finishedItemsCount,
                'progress' => ($progressPercent > 100) ? 100 : $progressPercent,
                'is_done' => $items->every('is_done', 1),
                'last_scan' => $selectedDateScans->where('doc_num', $docNum)->first()->created_at ?? null,
            ];
        }

        return view('store.sodashboard', compact(
            'selectedDateScans', 
            'soProgress', 
            'selectedDate',
            'weeklyTotalScans',
            'weeklyActiveDocsCount',
            'weeklyFinishedItems'
        ));
    }
}

