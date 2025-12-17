<?php

namespace App\Http\Controllers;

use App\Models\BarcodePackagingDetail;
use App\Models\BarcodePackagingMaster;
use App\Models\MasterDataRogPartName;
use App\Models\Customer;
use App\Models\StoreBoxData;
use App\Models\AlcPeMasterData;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Support\Facades\Log;


class BarcodeController extends Controller
{
    public function index()
    {
        return view('barcodeinandout.index');
    }

    public function indexBarcode()
    {
        $barcodesFolder = public_path('barcodes');
        File::cleanDirectory($barcodesFolder);
        $datas = MasterDataRogPartName::get();

        return view('barcodeinandout.indexbarcode', compact('datas'));
    }

    public function missingbarcodeindex()
    {
        $datas = MasterDataRogPartName::get();

        return view('barcodeinandout.missingbarcodeindex', compact('datas'));
    }

    public function missingbarcodegenerator(Request $request)
    {
        $partNo = $request->input('partNo');
        $partDetails = preg_split('/\//', $partNo, 2);
        $partNumber = $partDetails[0];
        $partName = $partDetails[1] ?? '';

        $barcodesFolder = public_path('barcodes');
        File::cleanDirectory($barcodesFolder);

        // Retrieve and convert missing numbers to an array
        $missingNumbers = explode(',', $request->input('missingnumber'));

        // Count the number of missing numbers
        $missingNumbersCount = count($missingNumbers);

        // dd($missingNumbersCount);

        foreach ($missingNumbers as $missingNumber) {

            $barcodeData = $partNumber."\t".$missingNumber;

            $barcode = new DNS1D;

            // Use $spkNumber in the filename
            $filename = $partNumber.'-'.$missingNumber.'.png';

            // Save the barcode as a PNG image inside the barcodes folder
            $barcode->getBarcodePNGPath($barcodeData, 'C128', 2, 70, [0, 0, 0], false, $filename);

            // Generate the HTML for the barcode
            $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128');
            // URL to the saved barcode image
            $barcodeUrl = asset('barcodes/'.$filename);
            //    dd($barcodeUrl);

            // Generate the HTML for the barcode
            $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128', 2, 70);

            $barcodes[] = [
                'partno' => $partNumber,
                'partname' => $partName,
                'missingNumber' => $missingNumber,
                'barcodeHtml' => $barcodeHtml,
                'barcodeUrl' => $barcodeUrl,
            ];

        }

        return view('barcodeinandout.missingbarcode', ['barcodes' => $barcodes, 'partno' => $partNo]);

    }

    public function generateBarcode(Request $request)
    {
        // dd($request->all());
        // $spkNumbers = ['2201222', '2292422', '1299922'];
        // $quantities = ['200', '1200', '100'];
        // $warehouses = ['FG', 'RM', 'RW'];

        $partno = $request->partNo;

        $partDetails = preg_split('/\//', $partno, 2);
        $partNumber = $partDetails[0];
        $partName = $partDetails[1] ?? '';
        // dd($partName);
        $defaultquantity = 1;
        $defaultwarehouse = 'IND';

        $startnum = $request->startNumber;
        $quantity = $request->quantity;
        $looping = $quantity - $startnum;
        $barcodes = [];

        $barcodesFolder = public_path('barcodes');
        File::cleanDirectory($barcodesFolder);

        for ($i = 0; $i <= $looping; $i++) {

            // Format the data as required ( DI SAP HARUS MENGGUNAKAN TAB )

            // $barcodeData = $partno . "\t" . $startnum . "\t" . $warehouse . "\t" . $incrementNumber;

            $barcodeData = $partNumber."\t".$defaultquantity."\t".$defaultwarehouse."\t".$startnum;
            // Generate the barcode using DNS1D (1D Barcode)
            $barcode = new DNS1D;

            // Use $spkNumber in the filename
            $filename = preg_replace('/[()#,.\\s&]+(?<!png)/i', '', $partNumber).'-'.$defaultquantity.'-'.$defaultwarehouse.'-'.$startnum.'.png';
            $filename = preg_replace('/"/', '-', $filename);
            $filename = preg_replace('/-+/', '-', $filename);

            $lowercaseFilename = strtolower($filename);
            // dd($lowercaseFilename);

            if (! File::exists($barcodesFolder)) {
                File::makeDirectory($barcodesFolder, 0755, true); // 0755 is the permission, true for recursive creation
            }

            // Save the barcode as a PNG image inside the barcodes folder
            $barcode->getBarcodePNGPath($barcodeData, 'C128', 1, 40, [0, 0, 0], false);

            // Generate the HTML for the barcode
            $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128');
            // URL to the saved barcode image
            $barcodeUrl = asset('barcodes/'.$lowercaseFilename);
            // Generate the HTML for the barcode
            $barcodeHtml = $barcode->getBarcodeHTML($barcodeData, 'C128', 2, 70);

            $barcodes[] = [
                'partno' => $partNumber,
                'partname' => $partName,
                'quantity' => $quantity,
                'startnum' => $startnum,
                'barcodeHtml' => $barcodeHtml,
                'barcodeUrl' => $barcodeUrl,
            ];

            $startnum += 1;

        }

        return view('barcodeinandout.barcode', ['barcodes' => $barcodes, 'partno' => $partno,
            'quantity' => $quantity,
            'startnum' => $startnum, ]);
    }

    public function inandoutpage()
    {
        $masters = BarcodePackagingMaster::with('detailBarcode')->get();

        // Loop through each master record
        foreach ($masters as $master) {
            // Check if the detailBarcode relationship is empty
            if ($master->detailBarcode->isEmpty()) {
                // Delete the master record if it has no detailBarcode
                $master->delete();
            }
        }
        $customers = Customer::orderBy('name')->get();

        return view('barcodeinandout.inandoutpage' , compact('customers'));
    }

    public function processInAndOut(Request $request)
    {
        // dd($request->all());
        $barcodePackagingMaster = new BarcodePackagingMaster;
        $tanggalScanFull = Carbon::now('Asia/Bangkok')->format('Y-m-d H:i:s');
        $barcodePackagingMaster->dateScan = $tanggalScanFull;
        $warehouseType = $request->input('warehouseType');
        $location = $request->input('location');
        $customer = $request->input('customer_name');

        // Define prefix based on warehouse type and location
        switch ($warehouseType) {
            case 'in':
                $prefix = 'IN';
                break;
            case 'out':
                $prefix = 'OUT';
                break;
            default:
                $prefix = '';
                break;
        }

        // Add location suffix based on location
        switch ($location) {
            case 'jakarta':
                $suffix = 'JKT';
                break;
            case 'karawang':
                $suffix = 'KRW';
                break;
            default:
                $suffix = '';
                break;
        }

        // Merge prefix and suffix
        $prefixSuffix = $prefix.'/'.$suffix;

        // Validate and set position based on the merged prefix and suffix
        switch ($prefixSuffix) {
            case 'IN/JKT':
                $position = 'Jakarta';
                $HeaderScan = 'IN JAKARTA';
                break;
            case 'IN/KRW':
                $position = 'Karawang';
                $HeaderScan = 'IN KARAWANG';
                break;
            case 'OUT/JKT':
                $position = 'CustomerJakarta';
                $HeaderScan = 'OUT JAKARTA';
                break;
            case 'OUT/KRW':
                $position = 'CustomerKarawang';
                $HeaderScan = 'OUT KARAWANG';
                break;
            default:
                $position = 'Unknown'; // Or handle default case as needed
                break;
        }

        $barcodePackagingMaster->tipeBarcode = $warehouseType;
        $barcodePackagingMaster->location = $location;
        $barcodePackagingMaster->customer = $customer;

        $barcodePackagingMaster->save();

        // Retrieve the id of the newly created entry
        $id = $barcodePackagingMaster->id;
        $currentDate = date('Ymd');

        // Combine prefix, suffix, and random string to form the document number
        $noDokumen = 'PKG'.'/'.$prefixSuffix.'/'.$id.'/'.$currentDate;

        $barcodePackagingMaster->noDokumen = $noDokumen;
        // Output the generated document number for testing purposes

        $barcodePackagingMaster->save();

        return view('barcodeinandout.scanpage', compact('noDokumen', 'tanggalScanFull', 'position', 'HeaderScan', 'customer'));
    }

    public function storeInAndOut(Request $request)
    {
        $data = $request->all();
        // dd($request->all());
        // dd($data);
        $docnum = $request->noDokumen;

        $position = $request->input('position');
        
        $master = BarcodePackagingMaster::where('noDokumen', $docnum)->first();

        $idmaster = $master->id;

        $counter = 1;
        $successfulLabels = [];
        while (isset($data['partno'.$counter])) {

            $partNo = $data['partno'.$counter];
            $label = $data['label'.$counter];

            // Check for duplicates
            // Ambil position terbaru dari kombinasi partNo + label
                $latestPosition = BarcodePackagingDetail::where('partNo', $partNo)
                    ->where('label', $label)
                    ->latest('created_at') // atau ->latest('id') kalau pakai id auto increment
                    ->value('position');

                // Cek apakah posisi terbaru sama dengan yang sekarang di-scan
                $exists = ($latestPosition === $position);
         
                if (!$exists) {
                    $scanTime = $data['scantime' . $counter];
                    // dd($scanTime);
                    // Replace koma dengan spasi dan titik dengan colon
                    $scanTime = str_replace(['.', ','], [':', ' '], $scanTime);

                    // Parse format waktu
                    $formattedScanTime = \Carbon\Carbon::createFromFormat('d/m/Y H:i:s', $scanTime)
                        ->format('Y-m-d H:i:s');

                    // Cek apakah partNo valid di tabel store_box_data
                    $isValidPart = \App\Models\StoreBoxData::where('part_no', $partNo)->exists();

                    if ($isValidPart) {
                        BarcodePackagingDetail::create([
                            'masterId'  => $idmaster,
                            'noDokumen' => $data['noDokumen'],
                            'partNo'    => $partNo,
                            'quantity'  => null,
                            'label'     => $label,
                            'position'  => $data['position'],
                            'scantime'  => $formattedScanTime,
                        ]);

                        $successfulLabels[] = $label;
                    }
                }
            $counter++;
        }

           if (count($successfulLabels) > 0) {
                $labelsList = implode(', ', $successfulLabels);
                $successMessage = "BERHASIL SCAN " . count($successfulLabels) . " DATA dengan label: " . $labelsList;
            } else {
                $successMessage = "Tidak ada data baru yang berhasil discan (mungkin sudah ada atau part number tidak valid)";
            }

        return redirect()->route('inandout.index')->with('success', $successMessage);
    }

    public function barcodelist()
    {
        $items = BarcodePackagingMaster::with('detailbarcode')
        ->orderBy('created_at', 'desc')
        ->get();

        $result = [];

        foreach ($items as $item) {
            $masterId = $item->id;
            $dateScan = $item->dateScan;
            $noDokumen = $item->noDokumen;
            $finishDokumen = $item->finishDokumen;

            // Initialize the structure for this master record
            $result[$masterId] = [
                'dateScan' => $dateScan,
                'noDokumen' => $noDokumen,
                'tipeBarcode' => $item->tipeBarcode, // Add tipeBarcode here
                'location' => $item->location,
                'customer' => $item->customer,
            ];

            // Initialize arrays for noDokumen and finishDokumen if not already set
            if (! isset($result[$masterId][$noDokumen])) {
                $result[$masterId][$noDokumen] = [];
            }

            // Process detail records for noDokumen
            foreach ($item->detailbarcode as $detail) {
                if ($detail->noDokumen === $noDokumen) {
                    $result[$masterId][$noDokumen][] = [
                        'partNo' => $detail->partNo,
                        'quantity' => $detail->quantity,
                        'label' => $detail->label,
                        'scantime' => $detail->scantime,
                        'position' => $detail->position,
                    ];
                }
            }

        }

        // Convert associative array to simple array
        $result = array_values($result);

        return view('barcodeinandout.listfinishbarcode', compact('result'));
    }

    public function filter(Request $request)
    {
        $query = BarcodePackagingMaster::with('detailbarcode');

        if ($request->filled('tipeBarcode')) {
            $query->where('tipeBarcode', $request->tipeBarcode);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('dateScan')) {
            $query->whereDate('dateScan', $request->dateScan);
        }

        $items = $query->orderBy('created_at', 'desc')->get();
        
        // Proses data sama seperti di barcodelist()
        $result = [];
        foreach ($items as $item) {
            $masterId = $item->id;
            $dateScan = $item->dateScan;
            $noDokumen = $item->noDokumen;
            $finishDokumen = $item->finishDokumen;
            
            $result[$masterId] = [
                'dateScan' => $dateScan,
                'noDokumen' => $noDokumen,
                'tipeBarcode' => $item->tipeBarcode,
                'location' => $item->location,
                'customer' => $item->customer,
            ];
            
            if (!isset($result[$masterId][$noDokumen])) {
                $result[$masterId][$noDokumen] = [];
            }
            
            foreach ($item->detailbarcode as $detail) {
                if ($detail->noDokumen === $noDokumen) {
                    $result[$masterId][$noDokumen][] = [
                        'partNo' => $detail->partNo,
                        'quantity' => $detail->quantity,
                        'label' => $detail->label,
                        'scantime' => $detail->scantime,
                        'position' => $detail->position,
                    ];
                }
            }
        }
        
        $result = array_values($result);
        
        return view('barcodeinandout.partials.barcode_table', compact('result'));
    }


    public function latestitemdetails(Request $request)
    {
        // Fetch distinct part numbers for the dropdown
        $partNumbers = BarcodePackagingDetail::select('partNo')->distinct()->get();

        // Fetch all items
        $items = BarcodePackagingDetail::where('label', '!=', 'ADJUST')->get();

        // Create an associative array to hold the latest records
        $latestItems = [];

        // Iterate over each item
        foreach ($items as $item) {
            $key = $item->partNo.'|'.$item->label;

            // If the key doesn't exist or the current item's scantime is later, update the array
            if (! isset($latestItems[$key]) || $item->scantime > $latestItems[$key]->scantime) {
                $latestItems[$key] = $item;
            }
        }

        // Extract the values to get the final collection
        $latestItems = array_values($latestItems);

        // Group by partNo and sort by label within each group
        $groupedItems = [];
        foreach ($latestItems as $item) {
            $groupedItems[$item->partNo][] = $item;
        }

        // Sort each group by label
        foreach ($groupedItems as &$group) {
            usort($group, function ($a, $b) {
                return $a->label <=> $b->label;
            });
        }

        // Flatten the groups into a single array
        $sortedItems = array_merge(...array_values($groupedItems));

        // Apply filters
        if ($request->filled('partNo')) {
            $sortedItems = array_filter($sortedItems, function ($item) use ($request) {
                return $item->partNo == $request->input('partNo');
            });
        }

        if ($request->filled('scantime')) {
            $sortedItems = array_filter($sortedItems, function ($item) use ($request) {
                return $item->scantime == $request->input('scantime');
            });
        }

        if ($request->filled('position')) {
            $sortedItems = array_filter($sortedItems, function ($item) use ($request) {
                return $item->position == $request->input('position');
            });
        }

        return view('barcodeinandout.latestbarcodeitem', compact('sortedItems', 'partNumbers'));

    }

    public function historybarcodelist(Request $request)
    {
        $query = BarcodePackagingMaster::with(['detailbarcode' => function ($query) use ($request) {
            if ($request->has('partNo') && $request->partNo != '') {
                $query->where('partNo', $request->partNo);
            }
        }]);

        // Apply filters
        if ($request->has('datescan') && $request->datescan != '') {
            $query->whereDate('dateScan', $request->datescan);
        }

        if ($request->has('barcode_type') && $request->barcode_type != '') {
            $query->where('tipeBarcode', $request->barcode_type);
        }

        if ($request->has('location') && $request->location != '') {
            $query->where('location', $request->location);
        }

        $items = $query->get();

        $distinctPartNos = BarcodePackagingDetail::select('partNo')->distinct()->get();

        return view('barcodeinandout.historylisttable', compact('items', 'distinctPartNos'));
    }

    public function stockall($location = 'Jakarta')
    {
        // Define position mappings based on location
        $positionMapping = [
            'Jakarta' => ['position' => 'Jakarta', 'customerPosition' => 'CustomerJakarta'],
            'Karawang' => ['position' => 'Karawang', 'customerPosition' => 'CustomerKarawang'],
        ];

        // Check if the location exists in the mapping
        if (! array_key_exists($location, $positionMapping)) {
            abort(404, 'Location not found');
        }

        // Retrieve data based on location mapping
        $locationData = $positionMapping[$location];
        $datas = BarcodePackagingDetail::where('position', $locationData['position'])
            ->orWhere('position', $locationData['customerPosition'])
            ->get();

        $names = MasterDataRogPartName::get();

        $partNos = $datas->pluck('partNo')->unique();
        $balances = [];

        foreach ($partNos as $partNo) {
            // Calculate quantities based on location
            $locationQuantity = $datas->where('partNo', $partNo)
                ->where('position', $locationData['position'])
                ->count();

            $customerQuantity = $datas->where('partNo', $partNo)
                ->where('position', $locationData['customerPosition'])
                ->count();

            $balance = max($locationQuantity - $customerQuantity, 0);

            // Find the corresponding name data and extract the description
            $nameData = $names->first(function ($item) use ($partNo) {
                return strpos($item->name, "{$partNo}/") === 0;
            });

            // Extract description or provide default message
            $description = $nameData ? explode('/', $nameData->name, 2)[1] : 'No description available';

            // Add the partNo, description, and balance to the balances array
            $balances[] = [
                'partNo' => $partNo,
                'description' => $description,
                'balance' => $balance,
            ];
        }

        return view('barcodeinandout.stockallbarcode', compact('balances', 'location'));
    }


    public function addCustomer()
    {
        $customers = Customer::latest()->get();
        return view('barcodeinandout.customeradd', compact('customers'));
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        Customer::create([
            'name' => $request->name
        ]);

        return redirect()->back()->with('success', 'Customer berhasil ditambahkan!');
    }

    public function destroyCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->back()->with('success', 'Customer berhasil dihapus!');
    }

    public function summaryDashboard()
    {
        $partNumbers = BarcodePackagingDetail::select('partNo')->distinct()->pluck('partNo');
        $summaryData = [];

        foreach ($partNumbers as $partNo) {
            $labels = BarcodePackagingDetail::where('partNo', $partNo)
                ->with('masterBarcode')
                ->orderBy('scantime', 'desc')
                ->get()
                ->groupBy('label');

            $daijoQty = 0;
            $kiicQty = 0;
            $customerQty = 0;
            $detailPerLabel = [];

            foreach ($labels as $label => $rows) {
                $latest = $rows->first(); // data terbaru dari label ini
                $position = strtolower(trim($latest->position)); // normalisasi teks

                // Karena setiap row = 1 quantity, cukup tambahkan 1 per label valid
                if ($position === 'jakarta') {
                    $daijoQty += 1;
                } elseif ($position === 'karawang') {
                    $kiicQty += 1;
                } elseif (in_array($position, ['customerjakarta', 'customerkarawang'])) {
                    $customerQty += 1;
                }

                $detailPerLabel[] = [
                    'label' => $label,
                    'position' => $latest->position,
                    'last_transaction' => $latest->scantime,
                    'quantity' => 1, // karena 1 row = 1 quantity
                    'customer' => $latest->masterBarcode ? $latest->masterBarcode->customer : null,
                    'history' => $rows->map(function ($r) {
                        return [
                            'scantime' => $r->scantime,
                            'position' => $r->position,
                            'label' => $r->label,
                            'no_dokumen' => $r->noDokumen,
                            'created_at' => $r->created_at,
                        ];
                    })->toArray(),
                ];
            }

            $summaryData[] = [
                'part_no' => $partNo,
                'quantity_daijo' => $daijoQty,
                'quantity_kiic' => $kiicQty,
                'quantity_customer' => $customerQty,
                'total' => $daijoQty + $kiicQty + $customerQty,
                'details' => $detailPerLabel,
            ];
        }

        // dd($summaryData); // Debug dulu
        return view('barcode.store_summary', [
            'summaryData' => $summaryData,
        ]);
    }


    public function alcindex()
    {
        $items = AlcPeMasterData::orderBy('part_code')->get();
        return view('barcode.alcindex', compact('items'));
    }


    public function generateLabelYangeng40x15(Request $request)
    {
        $RS  = chr(30); // ␝
        $GS  = chr(29); // ␟
        $EOT = chr(4);  // ␄

        // 2️⃣ Ambil data part yang dipilih
        $part = AlcPeMasterData::where('part_code', $request->part_code)->firstOrFail();
        // dd($part);
        $truepartNumber = $part->part_code;
        $sequenceCode = $part->alc_code;
        $projectCode = $part->project_code;
        // dd($sequenceCode);
        // dd($projectCode);

        $identifier1 = 'PEA';
        $date1 = date('ymd'); // Format tanggal saat ini ddmmyy
        $sequence1 = '0000'; // Mulai dari 0000
        $baseIdentifier = $identifier1 . $date1 . $sequence1;  
        // dd($baseIdentifier);


        $labels = collect();

        $code = substr($baseIdentifier, 0, 3); // PEA
        $date = substr($baseIdentifier, 3, 6); // 251009
        $sequence = substr($baseIdentifier, 9); // 0000

        $sequenceLength = strlen($sequence); // simpan panjang sequence (4 digit)
        $startSequence = (int)$sequence; // convert ke integer


        $supplierCode = 'HAWX';
        // $identifier = 'PEA2510090000';
        $partNumber = $part->part_code;
        $truepartNumber = str_replace('-', '', $part->part_code);
        $sequenceCode = $part->alc_code;
        $engineeringOrderNumber = '';
        $trackingCode1 = '251009Y111@QAD:';
        $trackingCode2 = $part->qad;
        $trackingCode = $trackingCode1 . $trackingCode2;
        $specificInfo = '12345'; // optional
        $initialProductClassification = 'N'; // optional
        $businessUnit = 'ABC'; // optional

        //tambahin project code di sebelah PEA 
        // Bersihin Part number dari - 

          // Loop untuk generate multiple labels
        $labels = [];
        for ($i = $request->label_start; $i <= $request->label_end; $i++) {
            $currentSequence = $startSequence + $i;
            $identifier = $code . $date . str_pad($currentSequence, $sequenceLength, '0', STR_PAD_LEFT);

             // Format sesuai standar ISO/IEC 15434
            $dataMatrixString = "[)>"
            . $RS . "06"
            . $GS . "V" . $supplierCode
            // . $GS . "E" . $engineeringOrderNumber
            . $GS . "P" . $truepartNumber
            . $GS . "S" . $sequenceCode
            . $GS . "T" . $trackingCode
            . $GS . "1A" . $specificInfo
            . $GS . "M" . $initialProductClassification
            . $GS . "C" . $businessUnit
            . $GS . $RS . $EOT;

        // Generate DataMatrix
        $barcode = new DNS2D();
        $image = $barcode->getBarcodePNG($dataMatrixString, 'DATAMATRIX');
        // dd($dataMatrixString);
            
            $labels[] = [
                'identifier' => $identifier,
                'supplierCode' => $supplierCode,
                'sequenceCode' => $sequenceCode,
                'partNumber' => $partNumber,
                'projectCode' => $projectCode,
                'image' => $image,
            ];
        }

          
        return view('labelyanfeng40x15', compact('image', 'engineeringOrderNumber', 'sequenceCode', 'supplierCode', 'partNumber', 'trackingCode','identifier', 'labels'));
    }

    public function generateLabelYangeng25x10(Request $request)
    {
        $RS  = chr(30); // ␝
        $GS  = chr(29); // ␟
        $EOT = chr(4);  // ␄

        // 2️⃣ Ambil data part yang dipilih
        $part = AlcPeMasterData::where('part_code', $request->part_code)->firstOrFail();
        // dd($part);
        $truepartNumber = $part->part_code;
        $sequenceCode = $part->alc_code;
        $projectCode = $part->project_code;
        // dd($sequenceCode);
        // dd($projectCode);

        $identifier1 = 'PEA';
        $date1 = date('ymd'); // Format tanggal saat ini ddmmyy
        $sequence1 = '0000'; // Mulai dari 0000
        $baseIdentifier = $identifier1 . $date1 . $sequence1;  
        // dd($baseIdentifier);


        $labels = collect();

        $code = substr($baseIdentifier, 0, 3); // PEA
        $date = substr($baseIdentifier, 3, 6); // 251009
        $sequence = substr($baseIdentifier, 9); // 0000

        $sequenceLength = strlen($sequence); // simpan panjang sequence (4 digit)
        $startSequence = (int)$sequence; // convert ke integer


        $supplierCode = 'HAWX';
        // $identifier = 'PEA2510090000';
        $partNumber = $part->part_code;
        $truepartNumber = str_replace('-', '', $part->part_code);
        $sequenceCode = $part->alc_code;
        $engineeringOrderNumber = '';
        $trackingCode1 = '251009Y111@QAD:';
        $trackingCode2 = $part->qad;
        $trackingCode = $trackingCode1 . $trackingCode2;
        $specificInfo = '12345'; // optional
        $initialProductClassification = 'N'; // optional
        $businessUnit = 'ABC'; // optional

        //tambahin project code di sebelah PEA 
        // Bersihin Part number dari - 

          // Loop untuk generate multiple labels
        $labels = [];
        for ($i = $request->label_start; $i <= $request->label_end; $i++) {
            $currentSequence = $startSequence + $i;
            $identifier = $code . $date . str_pad($currentSequence, $sequenceLength, '0', STR_PAD_LEFT);

             // Format sesuai standar ISO/IEC 15434
            $dataMatrixString = "[)>"
            . $RS . "06"
            . $GS . "V" . $supplierCode
            // . $GS . "E" . $engineeringOrderNumber
            . $GS . "P" . $truepartNumber
            . $GS . "S" . $sequenceCode
            . $GS . "T" . $trackingCode
            . $GS . "1A" . $specificInfo
            . $GS . "M" . $initialProductClassification
            . $GS . "C" . $businessUnit
            . $GS . $RS . $EOT;

        // Generate DataMatrix
        $barcode = new DNS2D();
        $image = $barcode->getBarcodePNG($dataMatrixString, 'DATAMATRIX');
        // dd($dataMatrixString);
            
            $labels[] = [
                'identifier' => $identifier,
                'supplierCode' => $supplierCode,
                'sequenceCode' => $sequenceCode,
                'partNumber' => $partNumber,
                'projectCode' => $projectCode,
                'image' => $image,
            ];
        }

          
        return view('labelyanfeng25x10', compact('image', 'engineeringOrderNumber', 'sequenceCode', 'supplierCode', 'partNumber', 'trackingCode','identifier', 'labels'));
    }

    public function generateLabelYangeng50x35(Request $request)
    {
        $RS  = chr(30); // ␝
        $GS  = chr(29); // ␟
        $EOT = chr(4);  // ␄

        // 2️⃣ Ambil data part yang dipilih
        $part = AlcPeMasterData::where('part_code', $request->part_code)->firstOrFail();
        // dd($part);
        $truepartNumber = $part->part_code;
        $sequenceCode = $part->alc_code;
        $projectCode = $part->project_code;
        // dd($sequenceCode);
        // dd($projectCode);

        $identifier1 = 'PEA';
        $date1 = date('ymd'); // Format tanggal saat ini ddmmyy
        $sequence1 = '0000'; // Mulai dari 0000
        $baseIdentifier = $identifier1 . $date1 . $sequence1;  
        // dd($baseIdentifier);


        $labels = collect();

        $code = substr($baseIdentifier, 0, 3); // PEA
        $date = substr($baseIdentifier, 3, 6); // 251009
        $sequence = substr($baseIdentifier, 9); // 0000

        $sequenceLength = strlen($sequence); // simpan panjang sequence (4 digit)
        $startSequence = (int)$sequence; // convert ke integer


        $supplierCode = 'HAWX';
        // $identifier = 'PEA2510090000';
        $partNumber = $part->part_code;
        $truepartNumber = str_replace('-', '', $part->part_code);
        $sequenceCode = $part->alc_code;
        $engineeringOrderNumber = '';
        $trackingCode1 = '251009Y111@QAD:';
        $trackingCode2 = $part->qad;
        $trackingCode = $trackingCode1 . $trackingCode2;
        $specificInfo = '12345'; // optional
        $initialProductClassification = 'N'; // optional
        $businessUnit = 'ABC'; // optional

        //tambahin project code di sebelah PEA 
        // Bersihin Part number dari - 

          // Loop untuk generate multiple labels
        $labels = [];
        for ($i = $request->label_start; $i <= $request->label_end; $i++) {
            $currentSequence = $startSequence + $i;
            $identifier = $code . $date . str_pad($currentSequence, $sequenceLength, '0', STR_PAD_LEFT);

             // Format sesuai standar ISO/IEC 15434
            $dataMatrixString = "[)>"
            . $RS . "06"
            . $GS . "V" . $supplierCode
            // . $GS . "E" . $engineeringOrderNumber
            . $GS . "P" . $truepartNumber
            . $GS . "S" . $sequenceCode
            . $GS . "T" . $trackingCode
            . $GS . "1A" . $specificInfo
            . $GS . "M" . $initialProductClassification
            . $GS . "C" . $businessUnit
            . $GS . $RS . $EOT;

        // Generate DataMatrix
        $barcode = new DNS2D();
        $image = $barcode->getBarcodePNG($dataMatrixString, 'DATAMATRIX');
        // dd($dataMatrixString);
            
            $labels[] = [
                'identifier' => $identifier,
                'supplierCode' => $supplierCode,
                'sequenceCode' => $sequenceCode,
                'partNumber' => $partNumber,
                'projectCode' => $projectCode,
                'image' => $image,
            ];
        }

          
        return view('labelyanfeng50x35', compact('image', 'engineeringOrderNumber', 'sequenceCode', 'supplierCode', 'partNumber', 'trackingCode','identifier', 'labels'));
    }

    public function generateAllLabelYangeng(Request $request)
{
    $RS  = chr(30); // ␝
    $GS  = chr(29); // ␟
    $EOT = chr(4);  // ␄

    // ✅ Ambil semua part
    // $parts = AlcPeMasterData::all();

    // $parts = AlcPeMasterData::where('ukuran_label', '!=', '25 x 10')->get();

    // $parts = AlcPeMasterData::where('ukuran_label', '25 x 10')->get();

    $partCodes = [
        '83930-I6000NNB',
        '83930-I6000PPX',
        '83940-I6000NNB',
        '83940-I6000PPX',
    ];

    $parts = AlcPeMasterData::where('ukuran_label', '!=', '25 x 10')
                ->whereIn('part_code', $partCodes)
                ->get();
 

    if ($parts->isEmpty()) {
        return back()->with('error', 'Tidak ada data part di database!');
    }

    $labels = [];

    // Base config
    $identifier1 = 'PEA';
    $date1 = date('ymd');
    $supplierCode = 'HAWX';
    $specificInfo = '12345';
    $initialProductClassification = 'N';
    $businessUnit = 'ABC';
    $date = $date1;

    // ✅ Loop semua part
    foreach ($parts as $part) {
        $truepartNumber = str_replace('-', '', $part->part_code);
        $sequenceCode = $part->alc_code;
        $projectCode = $part->project_code;
        $partNumber = $part->part_code;
        $trackingCode = $date . 'Y111@QAD:' . $part->qad;

        // 🔹 Buat 6 label untuk tiap part
        for ($i = 1; $i <= 4; $i++) {
            // Format urutan label 0001, 0002, dst
            $sequenceFormatted = str_pad($i, 4, '0', STR_PAD_LEFT);
            $identifier = $identifier1 . $date1 . $sequenceFormatted;

            // Format GS1 DataMatrix
            $dataMatrixString = "[)>"
                . $RS . "06"
                . $GS . "V" . $supplierCode
                . $GS . "P" . $truepartNumber
                . $GS . "S" . $sequenceCode
                . $GS . "T" . $trackingCode
                . $GS . "1A" . $specificInfo
                . $GS . "M" . $initialProductClassification
                . $GS . "C" . $businessUnit
                . $GS . $RS . $EOT;

            // Generate DataMatrix
            $barcode = new DNS2D();
            $image = $barcode->getBarcodePNG($dataMatrixString, 'DATAMATRIX');

            $labels[] = [
                'identifier' => $identifier,
                'supplierCode' => $supplierCode,
                'sequenceCode' => $sequenceCode,
                'partNumber' => $partNumber,
                'projectCode' => $projectCode,
                'image' => $image,
                'part_id' => $part->id,
                'label_no' => $i, // Tambahan: urutan label 1–6
            ];
        }
    }

    return view('alllabelyanfeng', compact('labels'));
}

}
