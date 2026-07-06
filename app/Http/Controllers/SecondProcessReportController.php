<?php

namespace App\Http\Controllers;

use App\Models\SecondProcessReport;
use App\Models\SecondProcessMaterial;
use App\Models\SecondProcessHourlyProduction;
use App\Models\SecondProcessManpower;
use App\Models\SecondProcessNgRecord;
use App\Models\SecondProcessTrouble;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SecondProcessReportController extends Controller
{
    public function index(Request $request)
    {
        $query = SecondProcessReport::query();

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $reports = $query->orderBy('date', 'desc')->paginate(15);

        return view('second_process.index', compact('reports'));
    }

    public function create()
    {
        // Pre-populate some standard materials, ng records, etc. if needed
        $defaultPaints = [
            'Paint Primer',
            'Hardener',
            'Paint Basecoat',
            'Hardener',
            'Paint Topcoat',
            'Hardener'
        ];

        $defaultParts = [
            'WIP 1',
            'WIP 2',
            'WIP 3',
            'Repairan 1',
            'Repairan 2',
            'Repairan 3'
        ];

        $defaultNgs = [
            'SCRATCH',
            'DIRTY',
            'HAIR MARK',
            'DENTED',
            'OVER CUT'
        ];

        $defaultTroubles = [
            'Man',
            'Mesin',
            'Part',
            'PPS',
            'Lingkungan'
        ];

        return view('second_process.create', compact('defaultPaints', 'defaultParts', 'defaultNgs', 'defaultTroubles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Header
            'date' => 'required|date',
            'unit_line' => 'required|string',
            'shift' => 'required|string',
            'process_prod' => 'required|string',
            'model' => 'required|string',
            'part_number' => 'required|string',
            'part_name' => 'required|string',
            'customer' => 'required|string',
            'target_per_hour' => 'nullable|integer',
            'jml_input_wip' => 'nullable|integer',
            'repairan' => 'nullable|integer',
            'jumlah_output' => 'nullable|integer',
            'jumlah_ok' => 'nullable|integer',
            'jumlah_ng' => 'nullable|integer',
            'ng_prosentase' => 'nullable|numeric',
            'jml_ng_lebur' => 'nullable|integer',
            // Footer
            'next_production_schedule' => 'nullable|array',
            'next_production_schedule.*' => 'nullable|string',
            'absent_employees' => 'nullable|string',
            'production_notes' => 'nullable|string',
            'created_by_name' => 'nullable|string',
            'pqc_name' => 'nullable|string',
            'acknowledged_by_name' => 'nullable|string',
            
            // Materials
            'materials' => 'nullable|array',
            'materials.*.type' => 'required|string',
            'materials.*.item_name' => 'required|string',
            'materials.*.lot_number' => 'nullable|string',
            'materials.*.visco' => 'nullable|string',
            'materials.*.qty' => 'nullable|integer',

            // Hourly Productions
            'hourly' => 'nullable|array',
            'hourly.*.hour_ke' => 'required|integer',
            'hourly.*.ok_qty' => 'nullable|integer',
            'hourly.*.acumulasi_qty' => 'nullable|integer',

            // Manpower
            'manpower' => 'nullable|array',
            'manpower.*.role' => 'required|string',
            'manpower.*.no' => 'required|integer',
            'manpower.*.name' => 'nullable|string',

            // NG Records
            'ngs' => 'nullable|array',
            'ngs.*.ng_name' => 'required|string',
            'ngs.*.hour_1' => 'nullable|integer',
            'ngs.*.hour_2' => 'nullable|integer',
            'ngs.*.hour_3' => 'nullable|integer',
            'ngs.*.hour_4' => 'nullable|integer',
            'ngs.*.hour_5' => 'nullable|integer',
            'ngs.*.hour_6' => 'nullable|integer',
            'ngs.*.hour_7' => 'nullable|integer',
            'ngs.*.hour_8' => 'nullable|integer',
            'ngs.*.hour_9' => 'nullable|integer',
            'ngs.*.hour_10' => 'nullable|integer',
            'ngs.*.hour_11' => 'nullable|integer',
            'ngs.*.hour_12' => 'nullable|integer',
            'ngs.*.total_ng' => 'nullable|integer',
            'ngs.*.ng_input_item' => 'nullable|string',
            'ngs.*.ng_input_qty' => 'nullable|integer',

            // Troubles
            'troubles' => 'nullable|array',
            'troubles.*.penyebab' => 'required|string',
            'troubles.*.penanganan' => 'nullable|string',
            'troubles.*.loss_time' => 'nullable|string',
        ]);

        // Server-side calculation & validation of totals
        $jumlah_ok = 0;
        if (isset($validated['hourly'])) {
            foreach ($validated['hourly'] as $hour) {
                $jumlah_ok += (int)($hour['ok_qty'] ?? 0);
            }
        }

        $jumlah_ng = 0;
        if (isset($validated['ngs'])) {
            foreach ($validated['ngs'] as $key => $ng) {
                $rowTotal = 0;
                for ($h = 1; $h <= 12; $h++) {
                    $rowTotal += (int)($ng['hour_' . $h] ?? 0);
                }
                $validated['ngs'][$key]['total_ng'] = $rowTotal;
                $jumlah_ng += $rowTotal;
            }
        }

        $jumlah_output = $jumlah_ok + $jumlah_ng;
        $ng_prosentase = 0;
        if ($jumlah_output > 0) {
            $ng_prosentase = round(($jumlah_ng / $jumlah_output) * 100, 2);
        }

        $validated['jumlah_ok'] = $jumlah_ok;
        $validated['jumlah_ng'] = $jumlah_ng;
        $validated['jumlah_output'] = $jumlah_output;
        $validated['ng_prosentase'] = $ng_prosentase;

        DB::transaction(function () use ($validated) {
            // 1. Create Report
            $report = SecondProcessReport::create($validated);

            // 2. Create Materials
            if (isset($validated['materials'])) {
                foreach ($validated['materials'] as $material) {
                    if (!empty($material['item_name']) || !empty($material['lot_number'])) {
                        $report->materials()->create($material);
                    }
                }
            }

            // 3. Create Hourly Productions
            if (isset($validated['hourly'])) {
                foreach ($validated['hourly'] as $hour) {
                    $report->hourlyProductions()->create($hour);
                }
            }

            // 4. Create Manpower
            if (isset($validated['manpower'])) {
                foreach ($validated['manpower'] as $mp) {
                    if (!empty($mp['name'])) {
                        $report->manpowers()->create($mp);
                    }
                }
            }

            // 5. Create NG Records
            if (isset($validated['ngs'])) {
                foreach ($validated['ngs'] as $ng) {
                    if (!empty($ng['ng_name'])) {
                        $report->ngRecords()->create($ng);
                    }
                }
            }

            // 6. Create Troubles
            if (isset($validated['troubles'])) {
                foreach ($validated['troubles'] as $trouble) {
                    if (!empty($trouble['penanganan']) || !empty($trouble['loss_time'])) {
                        $report->troubles()->create($trouble);
                    }
                }
            }
        });

        return redirect()->route('second-process-reports.index')
            ->with('success', 'Report created successfully.');
    }

    public function show($id)
    {
        $report = SecondProcessReport::with([
            'materials',
            'hourlyProductions',
            'manpowers',
            'ngRecords',
            'troubles'
        ])->findOrFail($id);

        return view('second_process.show', compact('report'));
    }

    public function edit($id)
    {
        $report = SecondProcessReport::with([
            'materials',
            'hourlyProductions',
            'manpowers',
            'ngRecords',
            'troubles'
        ])->findOrFail($id);

        return view('second_process.edit', compact('report'));
    }

    public function update(Request $request, $id)
    {
        $report = SecondProcessReport::findOrFail($id);

        $validated = $request->validate([
            // Header
            'date' => 'required|date',
            'unit_line' => 'required|string',
            'shift' => 'required|string',
            'process_prod' => 'required|string',
            'model' => 'required|string',
            'part_number' => 'required|string',
            'part_name' => 'required|string',
            'customer' => 'required|string',
            'target_per_hour' => 'nullable|integer',
            'jml_input_wip' => 'nullable|integer',
            'repairan' => 'nullable|integer',
            'jumlah_output' => 'nullable|integer',
            'jumlah_ok' => 'nullable|integer',
            'jumlah_ng' => 'nullable|integer',
            'ng_prosentase' => 'nullable|numeric',
            'jml_ng_lebur' => 'nullable|integer',
            // Footer
            'next_production_schedule' => 'nullable|array',
            'next_production_schedule.*' => 'nullable|string',
            'absent_employees' => 'nullable|string',
            'production_notes' => 'nullable|string',
            'created_by_name' => 'nullable|string',
            'pqc_name' => 'nullable|string',
            'acknowledged_by_name' => 'nullable|string',
            
            // Materials
            'materials' => 'nullable|array',
            'materials.*.type' => 'required|string',
            'materials.*.item_name' => 'required|string',
            'materials.*.lot_number' => 'nullable|string',
            'materials.*.visco' => 'nullable|string',
            'materials.*.qty' => 'nullable|integer',

            // Hourly Productions
            'hourly' => 'nullable|array',
            'hourly.*.hour_ke' => 'required|integer',
            'hourly.*.ok_qty' => 'nullable|integer',
            'hourly.*.acumulasi_qty' => 'nullable|integer',

            // Manpower
            'manpower' => 'nullable|array',
            'manpower.*.role' => 'required|string',
            'manpower.*.no' => 'required|integer',
            'manpower.*.name' => 'nullable|string',

            // NG Records
            'ngs' => 'nullable|array',
            'ngs.*.ng_name' => 'required|string',
            'ngs.*.hour_1' => 'nullable|integer',
            'ngs.*.hour_2' => 'nullable|integer',
            'ngs.*.hour_3' => 'nullable|integer',
            'ngs.*.hour_4' => 'nullable|integer',
            'ngs.*.hour_5' => 'nullable|integer',
            'ngs.*.hour_6' => 'nullable|integer',
            'ngs.*.hour_7' => 'nullable|integer',
            'ngs.*.hour_8' => 'nullable|integer',
            'ngs.*.hour_9' => 'nullable|integer',
            'ngs.*.hour_10' => 'nullable|integer',
            'ngs.*.hour_11' => 'nullable|integer',
            'ngs.*.hour_12' => 'nullable|integer',
            'ngs.*.total_ng' => 'nullable|integer',
            'ngs.*.ng_input_item' => 'nullable|string',
            'ngs.*.ng_input_qty' => 'nullable|integer',

            // Troubles
            'troubles' => 'nullable|array',
            'troubles.*.penyebab' => 'required|string',
            'troubles.*.penanganan' => 'nullable|string',
            'troubles.*.loss_time' => 'nullable|string',
        ]);

        // Server-side calculation & validation of totals
        $jumlah_ok = 0;
        if (isset($validated['hourly'])) {
            foreach ($validated['hourly'] as $hour) {
                $jumlah_ok += (int)($hour['ok_qty'] ?? 0);
            }
        }

        $jumlah_ng = 0;
        if (isset($validated['ngs'])) {
            foreach ($validated['ngs'] as $key => $ng) {
                $rowTotal = 0;
                for ($h = 1; $h <= 12; $h++) {
                    $rowTotal += (int)($ng['hour_' . $h] ?? 0);
                }
                $validated['ngs'][$key]['total_ng'] = $rowTotal;
                $jumlah_ng += $rowTotal;
            }
        }

        $jumlah_output = $jumlah_ok + $jumlah_ng;
        $ng_prosentase = 0;
        if ($jumlah_output > 0) {
            $ng_prosentase = round(($jumlah_ng / $jumlah_output) * 100, 2);
        }

        $validated['jumlah_ok'] = $jumlah_ok;
        $validated['jumlah_ng'] = $jumlah_ng;
        $validated['jumlah_output'] = $jumlah_output;
        $validated['ng_prosentase'] = $ng_prosentase;

        DB::transaction(function () use ($report, $validated) {
            // Update main report
            $report->update($validated);

            // Re-create materials
            $report->materials()->delete();
            if (isset($validated['materials'])) {
                foreach ($validated['materials'] as $material) {
                    if (!empty($material['item_name']) || !empty($material['lot_number'])) {
                        $report->materials()->create($material);
                    }
                }
            }

            // Re-create hourly productions
            $report->hourlyProductions()->delete();
            if (isset($validated['hourly'])) {
                foreach ($validated['hourly'] as $hour) {
                    $report->hourlyProductions()->create($hour);
                }
            }

            // Re-create manpower
            $report->manpowers()->delete();
            if (isset($validated['manpower'])) {
                foreach ($validated['manpower'] as $mp) {
                    if (!empty($mp['name'])) {
                        $report->manpowers()->create($mp);
                    }
                }
            }

            // Re-create NG records
            $report->ngRecords()->delete();
            if (isset($validated['ngs'])) {
                foreach ($validated['ngs'] as $ng) {
                    if (!empty($ng['ng_name'])) {
                        $report->ngRecords()->create($ng);
                    }
                }
            }

            // Re-create troubles
            $report->troubles()->delete();
            if (isset($validated['troubles'])) {
                foreach ($validated['troubles'] as $trouble) {
                    if (!empty($trouble['penanganan']) || !empty($trouble['loss_time'])) {
                        $report->troubles()->create($trouble);
                    }
                }
            }
        });

        return redirect()->route('second-process-reports.index')
            ->with('success', 'Report updated successfully.');
    }

    public function destroy($id)
    {
        $report = SecondProcessReport::findOrFail($id);
        $report->delete(); // child rows will be deleted via database foreign key cascade delete

        return redirect()->route('second-process-reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function searchItems(Request $request)
    {
        $query = $request->get('query');
        if (!$query) {
            return response()->json([]);
        }

        $items = \App\Models\MasterAllItem::where('item_code', 'LIKE', "%{$query}%")
            ->orWhere('item_description', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json($items);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('query');
        if (!$query) {
            return response()->json([]);
        }

        $customers = \App\Models\Customer::where('name', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get();

        return response()->json($customers);
    }
}
