<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Services\Production\ProductionReportService;

class ProductionReportController extends Controller
{
    public function __construct(
        private readonly ProductionReportService $productionReportService
    ) {}

    public function index(Request $request)
    {
        // User bisa pilih tanggal, default hari ini
        $date = $request->date ?? Carbon::today()->format('Y-m-d');
        
        // Panggil service untuk handle semua business logic (ambil dan format data laporan)
        $result = $this->productionReportService->getDailyReportData($date);

        return view('reports-production-daily', [
            'date' => $date,
            'data' => $result
        ]);
    }
}
