<?php

namespace App\Http\Controllers;

use App\Models\CustomBarcodeLog;
use App\Models\MasterListItem;
use App\Models\SpkItemHistory;
use Illuminate\Http\Request;
use Milon\Barcode\DNS1D;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class InitialBarcodeController extends Controller
{
    public function index()
    {
        $tipeMesins = MasterListItem::distinct()->pluck('tipe_mesin');
        // dd($tipeMesins);

        return view('barcode.index', compact('tipeMesins'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'tipe_mesin' => 'required',
        ]);

        // Fetch items with the selected tipe_mesin
        $items = MasterListItem::where('tipe_mesin', $request->tipe_mesin)->get();
        $labelCount = 1;

        return view('barcode.generate', compact('items', 'labelCount'));
    }

    public function manualgenerate()
    {
        return view('barcode.generatemanualbarcode');
    }

    public function generateBarcode(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string',
            'quantity' => 'required|integer',
            'warehouse' => 'required|string',
            'label' => 'required|integer',
        ]);

        $item_code = $request->input('item_code');
        $quantity = $request->input('quantity');
        $warehouse = $request->input('warehouse');
        $labelCount = $request->input('label');

        $barcodes = [];

        $barcodeGenerator = new DNS1D;

        for ($i = 1; $i <= $labelCount; $i++) {
            $barcodeData = "{$item_code}\t{$quantity}\t{$warehouse}\t{$i}";
            $barcode = $barcodeGenerator->getBarcodeHTML($barcodeData, 'C128');

            $barcodes[] = [
                'barcode' => $barcode,
                'item_code' => $item_code,
                'quantity' => $quantity,
                'label' => $i,
            ];
        }

        return view('barcode.barcode_result', compact('barcodes'));
    }

    public function customGenerateForm(Request $request)
    {
        $items = MasterListItem::orderBy('item_code')->get();
        $logs = CustomBarcodeLog::latest()->take(20)->get();

        return view('barcode.custom_generate_form', compact('items', 'logs'));
    }

    public function customGenerateLogs(Request $request)
    {
        $query = CustomBarcodeLog::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%")
                    ->orWhere('spk_number', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('operator', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $logs = $query->latest()->paginate(25)->withQueryString();

        $stats = [
            'total_print_jobs' => CustomBarcodeLog::count(),
            'total_labels_printed' => CustomBarcodeLog::sum('total_labels'),
            'today_print_jobs' => CustomBarcodeLog::whereDate('created_at', today())->count(),
            'today_labels_printed' => CustomBarcodeLog::whereDate('created_at', today())->sum('total_labels'),
        ];

        return view('barcode.custom_generate_logs', compact('logs', 'stats'));
    }

    public function getSpksByItem(Request $request)
    {
        $itemCode = $request->input('item_code');
        
        $spks = SpkItemHistory::where('item_code', $itemCode)
            ->whereNotNull('spk_number')
            ->distinct()
            ->pluck('spk_number')
            ->toArray();

        return response()->json($spks);
    }

    public function customGeneratePrint(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string',
            'spk_number' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'warehouse' => 'required|string',
            'start_label' => 'required|integer|min:1',
            'end_label' => 'required|integer|min:1|gte:start_label',
            'shift' => 'required|string|in:I,II,III',
            'prod_date' => 'nullable|date',
            'operator' => 'nullable|string',
            'customer' => 'nullable|string',
            'is_trial' => 'nullable|boolean',
            'remark' => 'nullable|string|max:1000',
        ]);

        $itemCode = $request->input('item_code');
        $spkNumber = $request->input('spk_number');
        $quantity = $request->input('quantity');
        $warehouse = $request->input('warehouse');
        $startLabel = (int) $request->input('start_label');
        $endLabel = (int) $request->input('end_label');
        $shift = $request->input('shift');
        $prodDate = $request->input('prod_date') ?: today()->toDateString();
        $operator = $request->input('operator') ?: '-';
        $customer = $request->input('customer') ?: '-';
        $isTrial = $request->boolean('is_trial');
        $remark = $request->input('remark');
        $totalLabels = ($endLabel - $startLabel) + 1;

        $item = MasterListItem::where('item_code', $itemCode)->firstOrFail();
        $itemName = $item->item_name;

        // Log the print action
        CustomBarcodeLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()?->name ?? 'Guest',
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'spk_number' => $spkNumber,
            'quantity' => $quantity,
            'warehouse' => $warehouse,
            'shift' => $shift,
            'start_label' => $startLabel,
            'end_label' => $endLabel,
            'total_labels' => $totalLabels,
            'prod_date' => $prodDate,
            'operator' => $operator,
            'customer' => $customer,
            'is_trial' => $isTrial,
            'remark' => $remark,
        ]);

        $labels = [];
        $writer = new PngWriter();

        for ($i = $startLabel; $i <= $endLabel; $i++) {
            // Format: spkno(tab)quantity(tab)warehouse(tab)nolabel
            $qrData = "{$spkNumber}\t{$quantity}\t{$warehouse}\t{$i}";
            if ($isTrial) {
                $qrData .= "\tTRIAL";
            }
            
            $qrCode = new QrCode(
                data: $qrData,
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 150,
                margin: 0
            );

            $qrResult = $writer->write($qrCode);
            $qrBase64 = base64_encode($qrResult->getString());

            $labels[] = [
                'label_no' => $i,
                'item_code' => $itemCode,
                'item_name' => $itemName,
                'spk_number' => $spkNumber,
                'warehouse' => $warehouse,
                'prod_date' => $prodDate,
                'operator' => $operator,
                'quantity' => $quantity,
                'shift' => $shift,
                'customer' => $customer,
                'qr_code_base64' => $qrBase64,
                'is_trial' => $isTrial,
            ];
        }

        return view('barcode.custom_generate_print', compact('labels'));
    }
}
