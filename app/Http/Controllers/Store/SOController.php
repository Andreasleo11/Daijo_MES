<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\ScannedData;
use App\Models\SoData;
use App\Models\UpdateLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SoImport;
use App\Exports\SoDataExport;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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

    public function process($docNum)
    {
        $allFinished = SoData::where('doc_num', $docNum)
            ->where(function ($query) {
                $query->where('is_finish', false)
                    ->orWhereNull('is_finish'); // Handle null values as well
            })
            ->doesntExist(); // If no such record exists, then all are finished
        // dd($allFinished);
        $allDone = SoData::where('doc_num', $docNum)
            ->where(function ($query) {
                $query->where('is_done', false)
                    ->orWhereNull('is_done'); // Handle null values as well
            })
            ->doesntExist(); // If no such record exists, then all are done
            // dd($allDone);
        $data = SoData::with('scannedData')->where('doc_num', $docNum)
            ->get()
            ->groupBy('item_code')
            ->map(function ($group) use ($docNum) {
                // Sum the quantities for each item_code
                $totalQuantity = $group->sum('quantity');

                // Keep one entry and update the quantity
                $entry = $group->first();
                
                $entry->quantity = $totalQuantity;

                $scannedCount = ScannedData::where('doc_num', $docNum)
                    ->where('item_code', $entry->item_code)
                    ->count();
                
                    
                $entry->scannedCount = $scannedCount;
               
                $packagingQuantity = $entry->packaging_quantity;
                 // Assuming `packaging_quantity` is a field in your SoData model
                // dd((int)ceil(($totalQuantity / $packagingQuantity)));
               
                // Check if scannedCount equals quantity / packaging_quantity and update is_finish
                if ($packagingQuantity > 0 && $scannedCount === (int)ceil(($totalQuantity / $packagingQuantity)) || $scannedCount >= (int)ceil(($totalQuantity / $packagingQuantity))) {
                    $entry->is_finish = 1;
                } else {
                    $entry->is_finish = 0; // Optionally set is_finish to 0 if the condition is not met
                }

                return $entry;
                
            })
            ->values(); // Reset the keys after grouping
            
        foreach ($data as $entry) {
            SoData::where('doc_num', $entry->doc_num)->update([
                'is_finish' => $entry->is_finish,
            ]);
        }
        // dd($data);
        $date = $data->isNotEmpty() ? $data->first()->posting_date : null;
        $customer = $data->isNotEmpty() ? $data->first()->customer : null;
        // Pass the data to the view

        $scandatas = ScannedData::where('doc_num', $docNum)-> // Order by item_code
            orderBy('label')  // Then order by label
                ->get()
                ->groupBy('item_code');

        // dd($data);
        return view('store.soresults', compact('data', 'docNum', 'date', 'customer', 'scandatas', 'allFinished', 'allDone'));
    }

    public function scanBarcode(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string',
            'quantity' => 'required|integer',
            'warehouse' => 'required|string',
            'label' => 'required|integer',
        ]);

        $doc_num = $request->input('so_number');
        $item_code = $request->input('item_code');
        $quantity = $request->input('quantity');
        $warehouse = $request->input('warehouse');
        $label = $request->input('label');

        // Fetch the item data
        $item = SoData::where('item_code', $item_code)->where('doc_num', $doc_num)->first();
       
        // dd($item);
        if (! $item) {
            return redirect()->back()->withErrors(['error' => 'Item not found']);
        }

        $existingScans = ScannedData::where('item_code', $item_code)->get();
        $scannedTotalQuantity = $existingScans->sum('quantity') + $quantity;
       
        if ($scannedTotalQuantity > $item->quantity) {
           
            return redirect()->back()->withErrors(['error' => 'All required CTN have been scanned / Quantity Tidak benar']);
        }

        // Check if the scanned data already exists
        $existingScan = ScannedData::where('item_code', $item_code)
            ->where('label', $label)
            ->first();

        if ($existingScan) {
            return redirect()->back()->withErrors(['error' => 'Data already scanned']);
        }

        // Add new scanned data
        ScannedData::create([
            'doc_num' => $doc_num,
            'item_code' => $item_code,
            'quantity' => $quantity,
            'warehouse' => $warehouse,
            'label' => $label,
        ]);

        return redirect()->back()->with('success', 'Barcode scanned successfully');
    }

    public function updateSoData($docNum)
    {
        // Update all SoData records with the given docNum
        SoData::where('doc_num', $docNum)
            ->update(['is_done' => true]);

        // Redirect back with a success message or to another route
        return redirect()->route('so.index')->with('status', 'All records updated successfully.');
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
}
