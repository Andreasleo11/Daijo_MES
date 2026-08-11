<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FirstPieceInspection;
use App\Models\IpqcInspection;
use App\Models\MasterCustomerDelivery;
use App\Models\MasterListItem;
use App\Models\SecondProcessReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SecondProcessReportController extends Controller
{
    public function index(Request $request)
    {
        $query = SecondProcessReport::query();

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Discrete filters
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }
        if ($request->filled('process_prod')) {
            $query->where('process_prod', $request->process_prod);
        }
        if ($request->filled('unit_line')) {
            $query->where('unit_line', 'LIKE', '%'.$request->unit_line.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Keyword search (model, part_number, customer, part_name)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('model', 'LIKE', "%{$search}%")
                    ->orWhere('part_number', 'LIKE', "%{$search}%")
                    ->orWhere('customer', 'LIKE', "%{$search}%")
                    ->orWhere('part_name', 'LIKE', "%{$search}%");
            });
        }

        // Summary stats from filtered query (before pagination)
        $summary = (clone $query)->selectRaw('
            COUNT(*) as total_reports,
            COALESCE(SUM(jumlah_output), 0) as total_output,
            COALESCE(SUM(jumlah_ok), 0) as total_ok,
            COALESCE(SUM(jumlah_ng), 0) as total_ng
        ')->first();

        $reports = $query->orderBy('date', 'desc')->paginate(25)->withQueryString();

        return view('second_process.index', compact('reports', 'summary'));
    }

    public function create(Request $request)
    {
        $report = new SecondProcessReport;
        
        // Auto-fill from dashboard parameters
        if ($request->has('unit_line')) {
            $report->unit_line = $request->input('unit_line');
        }
        if ($request->has('shift')) {
            $report->shift = $request->input('shift');
        }

        return view('second_process.create', compact('report'));
    }

    public function store(Request $request)
    {
        $report = new SecondProcessReport;
        $this->saveReport($request, $report);

        return redirect()->route('second-process-reports.index')
            ->with('success', 'Report created successfully.');
    }

    public function show($id)
    {
        $report = SecondProcessReport::with([
            'materials',
            'hourlyProductions',
            'manpowers',
            'ngRecords.hourlyDetails',
            'troubles',
        ])->findOrFail($id);

        $firstPiece = FirstPieceInspection::where('part_number', $report->part_number)
            ->whereDate('date', $report->date)
            ->orderBy('id', 'desc')
            ->first();

        // Look up linked IPQC inspection by natural keys
        $ipqcInspection = IpqcInspection::with(['records.attachments', 'attachments'])
            ->where('date', $report->date)
            ->where('part_number', $report->part_number)
            ->where('shift', $report->shift)
            ->where('unit_line', $report->unit_line)
            ->first();

        return view('second_process.show', compact('report', 'firstPiece', 'ipqcInspection'));
    }

    public function edit($id)
    {
        $report = SecondProcessReport::with([
            'materials',
            'hourlyProductions',
            'manpowers',
            'ngRecords.hourlyDetails',
            'troubles',
        ])->findOrFail($id);

        if ($report->status !== 'draft') {
            return redirect()->route('second-process-reports.show', $id)
                ->with('error', 'Only draft reports can be edited.');
        }

        return view('second_process.edit', compact('report'));
    }

    public function update(Request $request, $id)
    {
        $report = SecondProcessReport::findOrFail($id);

        if ($report->status !== 'draft') {
            return redirect()->route('second-process-reports.show', $id)
                ->with('error', 'Only draft reports can be updated.');
        }

        $this->saveReport($request, $report);

        return redirect()->route('second-process-reports.index')
            ->with('success', 'Report updated successfully.');
    }

    private function saveReport(Request $request, SecondProcessReport $report)
    {
        $validated = $request->validate([
            // Header
            'date' => 'required|date',
            'unit_line' => 'required|string',
            'shift' => 'required|string',
            'process_prod' => 'required|string',
            'status' => 'nullable|string',
            'output_destination' => 'nullable|string',
            'model' => 'nullable|string',
            'part_number' => 'required|string',
            'part_name' => 'nullable|string',
            'customer' => 'nullable|string',
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
            'ng_remarks' => 'nullable|string',
            'created_by_name' => 'nullable|string',
            'pqc_name' => 'nullable|string',
            'leader_name' => 'nullable|string',
            'acknowledged_by_name' => 'nullable|string',

            // Materials
            'materials' => 'nullable|array',
            'materials.*.type' => 'required|string',
            'materials.*.item_name' => 'required|string',
            'materials.*.lot_number' => 'nullable|string',
            'materials.*.visco' => 'nullable|string',
            'materials.*.qty' => 'nullable|numeric',
            'materials.*.uom' => 'nullable|string',
            'materials.*.mixing_ratio' => 'nullable|string',
            'materials.*.paint_type' => 'nullable|string',
            'materials.*.sub_type' => 'nullable|string',

            // Hourly Productions
            'hourly' => 'nullable|array',
            'hourly.*.hour_ke' => 'required|integer',
            'hourly.*.ok_qty' => 'nullable|integer',
            'hourly.*.ng_qty' => 'nullable|integer',
            'hourly.*.acumulasi_qty' => 'nullable|integer',
            'hourly.*.remark' => 'nullable|string',

            // Manpower
            'manpower' => 'nullable|array',
            'manpower.*.role' => 'required|string',
            'manpower.*.no' => 'required|integer',
            'manpower.*.name' => 'nullable|string',

            // NG Records
            'ngs' => 'nullable|array',
            'ngs.*.ng_category' => 'nullable|string',
            'ngs.*.ng_name' => 'required|string',
            'ngs.*.hours' => 'nullable|array',
            'ngs.*.hours.*' => 'nullable|integer',
            'ngs.*.total_ng' => 'nullable|integer',
            'ngs.*.ng_input_item' => 'nullable|string',
            'ngs.*.ng_input_qty' => 'nullable|integer',
            'ngs.*.remark' => 'nullable|string',

            // Troubles
            'troubles' => 'nullable|array',
            'troubles.*.penyebab' => 'required|string',
            'troubles.*.penanganan' => 'nullable|string',
            'troubles.*.loss_time' => 'nullable|string',
            'troubles.*.category' => 'nullable|string',
            'troubles.*.masalah' => 'nullable|string',
            'troubles.*.loss_time_minutes' => 'nullable|integer',
        ]);


        // Default integer fields
        $validated['target_per_hour'] = $validated['target_per_hour'] ?? 0;
        $validated['jml_input_wip'] = $validated['jml_input_wip'] ?? 0;
        $validated['repairan'] = $validated['repairan'] ?? 0;
        $validated['jml_ng_lebur'] = $validated['jml_ng_lebur'] ?? 0;

        // Server-side calculation of production totals
        $jumlah_ok = 0;
        if (isset($validated['hourly'])) {
            foreach ($validated['hourly'] as $hour) {
                $jumlah_ok += (int) ($hour['ok_qty'] ?? 0);
            }
        }

        $jumlah_ng = 0;
        if (isset($validated['ngs'])) {
            foreach ($validated['ngs'] as $key => $ng) {
                $rowTotal = 0;
                if (isset($ng['hours'])) {
                    foreach ($ng['hours'] as $val) {
                        $rowTotal += (int) $val;
                    }
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


        // Auto-assign status
        $validated['status'] = $validated['status'] ?? ($report->exists ? $report->status : 'draft');

        if (! $report->exists) {
            $validated['created_by_name'] = auth()->user()->name;
            if ($validated['status'] === 'submitted') {
                $validated['created_by_signed_at'] = now();
            } else {
                $validated['created_by_signed_at'] = null;
            }
            $validated['pqc_name'] = null;
            $validated['pqc_signed_at'] = null;
            $validated['leader_name'] = null;
            $validated['leader_signed_at'] = null;
            $validated['acknowledged_by_name'] = null;
            $validated['acknowledged_signed_at'] = null;
        } else {
            $validated['created_by_name'] = $report->created_by_name;
            $validated['created_by_signed_at'] = $report->created_by_signed_at;
            $validated['pqc_name'] = $report->pqc_name;
            $validated['pqc_signed_at'] = $report->pqc_signed_at;
            $validated['leader_name'] = $report->leader_name;
            $validated['leader_signed_at'] = $report->leader_signed_at;
            $validated['acknowledged_by_name'] = $report->acknowledged_by_name;
            $validated['acknowledged_signed_at'] = $report->acknowledged_signed_at;

            if ($validated['status'] === 'submitted' && empty($report->created_by_signed_at)) {
                $validated['created_by_signed_at'] = now();
            }
        }

        DB::transaction(function () use ($request, $report, $validated) {
            if ($report->exists) {
                // Delete old relations
                $report->materials()->delete();
                $report->hourlyProductions()->delete();
                $report->manpowers()->delete();
                $report->ngRecords()->delete();
                $report->troubles()->delete();

                $report->update($validated);
            } else {
                $report->fill($validated)->save();
            }

            // Create Materials
            if (isset($validated['materials'])) {
                foreach ($validated['materials'] as $material) {
                    if (! empty($material['item_name']) || ! empty($material['lot_number'])) {
                        $report->materials()->create($material);
                    }
                }
            }

            // Create Hourly Productions
            if (isset($validated['hourly'])) {
                foreach ($validated['hourly'] as $hour) {
                    $hour['ok_qty'] = (int) ($hour['ok_qty'] ?? 0);
                    $hour['ng_qty'] = (int) ($hour['ng_qty'] ?? 0);
                    $hour['acumulasi_qty'] = (int) ($hour['acumulasi_qty'] ?? 0);
                    $report->hourlyProductions()->create($hour);
                }
            }

            // Create Manpower
            if (isset($validated['manpower'])) {
                foreach ($validated['manpower'] as $mp) {
                    if (! empty($mp['name'])) {
                        $report->manpowers()->create($mp);
                    }
                }
            }

            // Create NG Records and hourly details
            if (isset($validated['ngs'])) {
                foreach ($validated['ngs'] as $ngData) {
                    if (! empty($ngData['ng_name'])) {
                        $ngRecord = $report->ngRecords()->create($ngData);

                        if (isset($ngData['hours'])) {
                            foreach ($ngData['hours'] as $hourKe => $val) {
                                $qty = (int) $val;
                                if ($qty > 0) {
                                    $ngRecord->hourlyDetails()->create([
                                        'hour_ke' => (int) $hourKe,
                                        'qty' => $qty,
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Create Troubles
            if (isset($validated['troubles'])) {
                foreach ($validated['troubles'] as $trouble) {
                    if (! empty($trouble['penyebab']) || ! empty($trouble['penanganan']) || ! empty($trouble['masalah'])) {
                        if (empty($trouble['category']) && ! empty($trouble['penyebab'])) {
                            $trouble['category'] = $trouble['penyebab'];
                        }
                        if (empty($trouble['loss_time_minutes']) && ! empty($trouble['loss_time'])) {
                            preg_match('/\d+/', $trouble['loss_time'], $matches);
                            $trouble['loss_time_minutes'] = isset($matches[0]) ? (int) $matches[0] : 0;
                        }
                        $trouble['loss_time_minutes'] = (int) ($trouble['loss_time_minutes'] ?? 0);
                        $report->troubles()->create($trouble);
                    }
                }
            }
        });
    }

    public function destroy($id)
    {
        $report = SecondProcessReport::findOrFail($id);
        $report->delete();

        return redirect()->route('second-process-reports.index')
            ->with('success', 'Report deleted successfully.');
    }

    public function searchItems(Request $request)
    {
        $query = $request->get('query');
        if (! $query) {
            return response()->json([]);
        }

        $items = MasterListItem::with('customer')
            ->where('item_code', 'LIKE', "%{$query}%")
            ->orWhere('item_name', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $rawCust = $item->customer?->customer_name ?? $item->customer_code;
                $custName = (!empty($rawCust) && $rawCust !== '0' && $rawCust !== '-') ? $rawCust : 'N/A';
                $rawModel = $item->project_code;
                $modelCode = (!empty($rawModel) && $rawModel !== '0' && $rawModel !== '-') ? $rawModel : 'N/A';
                return [
                    'id' => $item->id,
                    'item_code' => $item->item_code,
                    'item_name' => $item->item_name,
                    'item_description' => $item->item_name,
                    'project_code' => $modelCode,
                    'customer_name' => $custName,
                    'customer_code' => $item->customer_code,
                ];
            });

        return response()->json($items);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('query');
        if (! $query) {
            return response()->json([]);
        }

        $customers = MasterCustomerDelivery::where('customer_name', 'LIKE', "%{$query}%")
            ->orWhere('customer_code', 'LIKE', "%{$query}%")
            ->limit(20)
            ->get()
            ->map(function ($cust) {
                return [
                    'id' => $cust->id,
                    'customer_code' => $cust->customer_code,
                    'customer_name' => $cust->customer_name,
                    'name' => $cust->customer_name,
                ];
            });

        return response()->json($customers);
    }

    public function sign(Request $request, $id, $role)
    {
        $report = SecondProcessReport::findOrFail($id);
        $user = auth()->user();

        if (! $user->role || ! in_array($user->role->name, ['ADMIN', 'SECONDPROCESS', 'PRODUCTION', 'QUALITY'])) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to sign reports in the Second Process department.']);
        }

        switch ($role) {
            case 'checker':
                if ($report->status !== 'draft') {
                    return redirect()->back()->withErrors(['error' => 'Checker signature can only be applied to draft reports.']);
                }
                $report->update([
                    'created_by_name' => $user->name,
                    'created_by_signed_at' => now(),
                    'status' => 'submitted',
                ]);
                break;

            case 'pqc':
                if ($report->status !== 'submitted') {
                    return redirect()->back()->withErrors(['error' => 'PQC signature can only be applied to submitted reports.']);
                }

                // Check First Piece Approval Gate
                $firstPiece = FirstPieceInspection::where('part_number', $report->part_number)
                    ->whereDate('date', $report->date)
                    ->orderBy('id', 'desc')
                    ->first();

                if (! $firstPiece || ! $firstPiece->isApproved()) {
                    return redirect()->back()->withErrors([
                        'error' => "Cannot sign PQC approval: First Piece Inspection for part '{$report->part_number}' on {$report->date} is not approved by QC.",
                    ]);
                }

                $report->update([
                    'pqc_name' => $user->name,
                    'pqc_signed_at' => now(),
                    'status' => 'pqc_approved',
                ]);
                break;

            case 'leader':
                if ($report->status !== 'pqc_approved') {
                    return redirect()->back()->withErrors(['error' => 'Leader signature can only be applied after PQC approval.']);
                }
                $report->update([
                    'leader_name' => $user->name,
                    'leader_signed_at' => now(),
                    'status' => 'leader_approved',
                ]);
                break;

            case 'acknowledged':
                if ($report->status !== 'leader_approved') {
                    return redirect()->back()->withErrors(['error' => 'Supervisor signature can only be applied after Leader approval.']);
                }
                $report->update([
                    'acknowledged_by_name' => $user->name,
                    'acknowledged_signed_at' => now(),
                    'status' => 'acknowledged',
                ]);
                break;

            default:
                return redirect()->back()->withErrors(['error' => 'Invalid approval role.']);
        }

        return redirect()->route('second-process-reports.show', $id)
            ->with('success', ucfirst($role).' signature applied successfully.');
    }

    public function reject(Request $request, $id)
    {
        $report = SecondProcessReport::findOrFail($id);
        $user = auth()->user();

        if (! $user->role || ! in_array($user->role->name, ['ADMIN', 'SECONDPROCESS', 'PRODUCTION', 'QUALITY'])) {
            return redirect()->back()->withErrors(['error' => 'You are not authorized to reject reports in this department.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $report->update([
            'status' => 'draft',
            'created_by_signed_at' => null,
            'pqc_name' => null,
            'pqc_signed_at' => null,
            'leader_name' => null,
            'leader_signed_at' => null,
            'acknowledged_by_name' => null,
            'acknowledged_signed_at' => null,
            'ng_remarks' => trim(($report->ng_remarks ? $report->ng_remarks."\n" : '').'Rejected by '.$user->name.': '.$request->rejection_reason),
        ]);

        return redirect()->route('second-process-reports.show', $id)
            ->with('success', 'Report was successfully rejected and returned to Draft.');
    }
}
