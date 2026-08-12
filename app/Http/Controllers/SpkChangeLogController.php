<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SpkChangeLog;
use App\Services\SpkMasterService;
use Illuminate\Support\Facades\DB;

class SpkChangeLogController extends Controller
{
    public function index(Request $request)
    {
        $query = SpkChangeLog::with('masterItem')->latest();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('spk_number', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('change_type')) {
            $query->where('change_type', $request->change_type);
        }

        if ($request->filled('batch_id')) {
            $query->where('sync_batch_id', $request->batch_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get unique batch list for filter dropdown
        $batches = SpkChangeLog::select('sync_batch_id', DB::raw('MAX(created_at) as created_at'))
            ->groupBy('sync_batch_id')
            ->orderByDesc('created_at')
            ->take(30)
            ->get();

        // Summary statistics
        $stats = [
            'total_changes' => SpkChangeLog::count(),
            'total_new' => SpkChangeLog::where('change_type', 'NEW')->count(),
            'total_qty_change' => SpkChangeLog::where('change_type', 'QTY_CHANGE')->count(),
            'total_removed' => SpkChangeLog::where('change_type', 'REMOVED')->count(),
            'last_sync' => SpkChangeLog::latest()->value('created_at'),
        ];

        return view('spk.changes-index', compact('logs', 'batches', 'stats'));
    }

    public function getHistory($spkNumber)
    {
        $history = SpkChangeLog::with('masterItem')
            ->where('spk_number', $spkNumber)
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'spk_number' => $spkNumber,
            'history' => $history
        ]);
    }

    public function triggerSync(SpkMasterService $spkService, Request $request)
    {
        try {
            $result = $spkService->SyncData();

            if ($request->ajax()) {
                return $result;
            }

            return redirect()->route('spk.changes.index')->with('success', 'Sinkronisasi SPK berhasil dijalankan!');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }

            return redirect()->route('spk.changes.index')->with('error', 'Gagal sinkron SPK: ' . $e->getMessage());
        }
    }
}
