<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductionReport;
use App\Models\ProductionScannedData;
use App\Models\SpkMaster;
use App\Models\Delivery\sapInventoryFg;
use App\Models\User;
use App\Models\MachineJob;
use App\Models\OperatorUser;
use App\Models\DailyItemCode;
use App\Models\MouldChangeLog;
use App\Models\AdjustMachineLog;
use App\Models\MasterZone;
use App\Models\MasterListItem;
use App\Models\ZoneLog;
use App\Models\ZonePengawas;
use App\Models\RepairMachineLog;
use App\Models\HourlyRemark;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Production\ProductionDashboardService;


class ProductionDashboardController extends Controller
{
    public function __construct(
        private readonly ProductionDashboardService $productionDashboardService
    ) {}

    public function index(Request $request)
    {
        $selectedDate = $request->input('date', Carbon::now()->toDateString());
        $machineName = $request->input('machine_name', '');
        
        $structuredData = $this->productionDashboardService->getDashboardData($selectedDate, $machineName);
        

        $machineNames = [
            '0350F',
            '0450F',
            '0450G',
            '0450H',
            '0450I',
            '0450J',
            '0550B',
            '0650D',
            '0650E',
            '0850D',
            'K2800A',
            'K2100A',
            'K1400A',
            'K1400B',
            'K1400C',
            'K0900A',
            'K0900B',
            'K0650A',
            'K0650B',
            'K0750A',
            'K0750B',
            'K0450A',
        ];
        
        $machines = User::distinct()
            ->whereIn('id', MachineJob::pluck('user_id'))
            ->whereIn('name', $machineNames)
            ->pluck('name', 'id');
            
        // dd($structuredData);
        return view('dashboards.dashboard-master-production', compact('structuredData', 'machines', 'selectedDate'));
    }

    public function getMachinesByItem(Request $request)
    {
        $itemCode = $request->input('item_code');

        $results = DailyItemCode::with('user')
        ->where('item_code', $itemCode)
        ->get()
        ->map(function ($dic) {
            return [
                'machine' => $dic->user->name ?? 'Unknown',
                'date' => Carbon::parse($dic->schedule_date)->format('Y-m-d'),
            ];
        })
        ->unique(fn ($item) => $item['machine'] . '|' . $item['date'])
        ->values(); // ⬅⬅ Ini penting biar bisa pakai forEach() di frontend

        return response()->json($results);
    }

    public function adminView()
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $fourDaysAgo = now()->subDays(4)->toDateString();
        // dd($fourDaysAgo);

        $dailyItemCodes = DailyItemCode::with('user', 'hourlyRemarks', 'scannedData') // Load relasi user
            ->whereIn('start_date', [$yesterday, $today, $tomorrow, $fourDaysAgo])
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->get();

        return view('admin.dailyitemcodesindex', compact('dailyItemCodes', 'today', 'yesterday', 'tomorrow', 'fourDaysAgo'));
    }

    public function setStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'nullable|in:1,null',
        ]);

        $code = DailyItemCode::findOrFail($id);
        $code->is_done = $request->status === 'null' ? null : 1;
        $code->save();

        return back()->with('status', 'Status updated successfully.');
    }

    public function destroyHourlyRemark($id)
    {
        $hr = HourlyRemark::findOrFail($id);
        $hr->delete();

        return back()->with('success', 'Hourly remark berhasil dihapus.');
    }


}