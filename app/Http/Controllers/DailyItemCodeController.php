<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyItemCodeRequest;
use App\Models\DailyItemCode;
use App\Models\MasterListItem;
use App\Models\delivery\sapInventoryFg;
use App\Models\delivery\sapLineProduction;
use App\Models\SpkMaster;
use App\Models\ProductionScannedData;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Production\DailyItemCodeService;

class DailyItemCodeController extends Controller
{
    public function __construct(
        private readonly DailyItemCodeService $dailyItemCodeService
    ) {}
    //view index untuk ppic assign item code ke mesin ( planning produksi harian)
    public function index()
    {
        $users = User::all();
        $dailyItemCodes = DailyItemCode::all();
        $itemCodes = sapInventoryFg::all()->pluck('item_code');
        
        return view('daily-item-codes.index', compact('dailyItemCodes', 'users', 'itemCodes'));
    }

    // view untuk ppic pilih tiap mesin dan assign item code per shift ( planning produksi harian)
    public function create(Request $request)
    {
        $machines = $this->dailyItemCodeService->getOperatorMachines();

        $selectedDate = $request->selected_date;
        $machineId = $request->machine_id;

        $selectedMachine = User::where('id', $machineId)->first();

        // Transform $selectedMachine->name using the helper function
        $transformedMachineName = $this->dailyItemCodeService->transformUsername($selectedMachine->name);

        $masterListItem = sapInventoryFg::get('item_code');
        $dailyItemCodes = DailyItemCode::all();

        return view('daily-item-codes.create', compact('machines', 'masterListItem', 'selectedDate', 'selectedMachine', 'dailyItemCodes'));
    }

    //ajax call di create view untuk show item code yang diketik user (PPIC)
    // Tambahkan method baru di controller untuk API endpoint
    public function getItemCodes(Request $request)
    {
        $search = $request->get('search', '');
        $limit = $request->get('limit', 100); // Limit hasil pencarian
        
        $query = MasterListItem::select('item_code');
        
        if ($search) {
            $query->where('item_code', 'LIKE', '%' . $search . '%');
        }
        
        $items = $query->limit($limit)->get();
        
        return response()->json([
            'items' => $items->map(function($item) {
                return [
                    'value' => $item->item_code,
                    'text' => $item->item_code
                ];
            })
        ]);
    }
    
    //ajax call di create view untuk calculate item code yang akan diassign ke mesin
    public function calculateItem(Request $request)
    {
        $data = $request->json()->all();

        $itemCode = $data['item_code'] ?? null;
        $quantity = $data['quantity'] ?? null;

        try {
            $stats = $this->dailyItemCodeService->calculateItemStats($itemCode, $quantity);
            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    //store data yang diinput oleh ppic seperti item code dan quantity ke mesin yang diassign 
    public function store(StoreDailyItemCodeRequest $request)
    {
        $validatedData = $request->validated();

        // Pass validation of shift sequence to service
        $validationError = $this->dailyItemCodeService->validateShiftsSequence($validatedData);
        if ($validationError) {
            return back()
                ->withErrors([
                    $validationError['field'] => $validationError['message'],
                ])
                ->withInput()
                ->with('error', 'There were errors in your form submission. Please correct them and try again.');
        }

        try {
            // Save the data to the DailyItemCodes table using the Service
            $this->dailyItemCodeService->assignItemCodes($validatedData);
        } catch (\InvalidArgumentException $e) {
            // Catch error if quantity > SPK maximum
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        return redirect()->route('daily-item-code.index')->with('success', 'Daily Item Codes assigned successfully.');
    }

    //view daily item code ( planning produksi harian)
    public function daily(Request $request)
    {
        $selectedDate = $request->query('date'); // Get the date from the query parameter
        $machines = $this->dailyItemCodeService->getOperatorMachines();
        return view('daily-item-codes.daily', compact('selectedDate', 'machines'));
    }

    //update data yang diinput oleh ppic seperti item code dan quantity ke mesin yang diassign (revisi per shift) 
    public function update(Request $request, $id){
        // dd($request->all());
        $validatedData = $request->validate([
            'item_code' => 'required|string|max:255',
            'quantity' => 'required|integer',
            'shift' => 'required|integer',
            'start_date' => 'required|date',
            'start_time' => 'required',
            'end_date' => 'required|date',
            'end_time' => 'required',
        ]);

        $dailyItemCode = DailyItemCode::findOrFail($id);
        $dailyItemCode->update($validatedData);

        return redirect()->back()->with('success', 'Daily Item Code updated successfully.');
    }

    //hold | unused| function untuk push data scan operator ke table untuk dikirim ke SAP pakai API 
    public function generateDataForSap()
    {
   
        // $startOfDay = Carbon::create(2025, 3, 17)->startOfDay();
        // $endOfDay = Carbon::create(2025, 3, 17)->endOfDay();
        $startOfDay = Carbon::now()->startOfDay();
        $endOfDay = Carbon::now()->endOfDay();

        
        // Query untuk mendapatkan data berdasarkan waktu yang dikonversi ke zona waktu Indonesia
        $rawData = ProductionScannedData::with(['parentDailyItemCode.user'])
        ->whereBetween('created_at', [$startOfDay, $endOfDay])
        ->where('processed', 0)
        ->selectRaw("
            dic_id,
            spk_code,
            item_code,
            SUM(quantity) as total_quantity,
            COUNT(*) as numbox,
            FLOOR(UNIX_TIMESTAMP(CONVERT_TZ(created_at, '+00:00', '+07:00')) / 900) AS interval_group
        ")
        ->groupBy('interval_group', 'spk_code', 'item_code', 'dic_id')
        ->orderBy('interval_group')
        ->get();
        // dd($rawData);

        // Menyusun data dengan interval waktu yang terbaca
        $data = $rawData->map(function ($row) {
            // Menentukan waktu mulai dan waktu akhir untuk interval
            $intervalStart = Carbon::createFromTimestamp($row->interval_group * 900)
                ->setTimezone('Asia/Jakarta') // Set ke zona waktu Indonesia
                ->format('H:i');

            // Interval berikutnya untuk menghitung waktu akhir
            $intervalEnd = Carbon::createFromTimestamp(($row->interval_group + 1) * 900)
                ->setTimezone('Asia/Jakarta')
                ->format('H:i');

            return [
                'interval' => $intervalStart . ' WIB - ' . $intervalEnd . ' WIB',
                'spk_code' => $row->spk_code,
                'item_code' => $row->item_code,
                'total_quantity' => $row->total_quantity,
                'numbox' => $row->numbox,
                'mesin' => optional($row->parentDailyItemCode->user)->name ?? 'N/A',
            ];
        });

        // Pastikan kita mengembalikan data ke view
        return view('send-api-table', compact('data'));
    }

    // function untuk menghapus jadwal yang sudah dibuat oleh PPIC 
    public function destroy($id)
    {
        $item = DailyItemCode::find($id);
    
        if (!$item) {
            return redirect()->back()->with('error', 'Item not found.');
        }
    
        $item->forceDelete();
    
        // return redirect()->back()->with('success', 'Daily Item Code deleted successfully!');
        return response()->json(['message' => 'Daily Item Code deleted successfully!']);
    }
    
}
