<?php

namespace App\Http\Controllers;

use App\Models\MasterListItem;
use App\Models\SpWorkOrder;
use App\Models\FirstPieceInspection;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SpWorkOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = SpWorkOrder::with(['creator', 'sessions.operator']);

        if ($request->filled('date_from')) {
            $query->whereDate('planned_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('planned_date', '<=', $request->date_to);
        }
        if ($request->filled('unit_line')) {
            $query->where('unit_line', $request->unit_line);
        }
        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('wo_number', 'LIKE', "%{$search}%")
                    ->orWhere('part_number', 'LIKE', "%{$search}%")
                    ->orWhere('part_name', 'LIKE', "%{$search}%")
                    ->orWhere('customer', 'LIKE', "%{$search}%");
            });
        }

        $workOrders = $query->orderBy('planned_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        $partNumbers = $workOrders->pluck('part_number')->filter()->unique();
        $firstPieceMap = FirstPieceInspection::whereIn('part_number', $partNumbers)
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('part_number');

        $lines = array_values(config('mes.sp_lines', []));
        $processes = ['Assembly', 'Painting', 'Buffing', 'Amplas', 'Trimming', 'Printing', 'Packing', 'Rework', 'Repair'];

        return view('sp_work_orders.index', compact('workOrders', 'firstPieceMap', 'lines', 'processes'));
    }

    public function create()
    {
        $woNumber = SpWorkOrder::generateWoNumber();
        $lines = array_values(config('mes.sp_lines', []));
        $processes = ['Assembly', 'Painting', 'Buffing', 'Amplas', 'Trimming', 'Printing', 'Packing', 'Rework', 'Repair'];

        return view('sp_work_orders.create', compact('woNumber', 'lines', 'processes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'wo_number' => 'required|string|unique:sp_work_orders,wo_number',
            'planned_date' => 'required|date',
            'unit_line' => 'required|string',
            'shift' => 'required|string',
            'process_prod' => 'required|string',
            'part_number' => 'required|string',
            'part_name' => 'required|string',
            'model' => 'nullable|string',
            'customer' => 'required|string',
            'target_qty' => 'required|integer|min:1',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status'] = 'planned';

        $wo = SpWorkOrder::create($validated);

        return redirect()->route('sp-work-orders.show', $wo->id)
            ->with('success', "Work Order {$wo->wo_number} created successfully.");
    }

    public function show($id)
    {
        $workOrder = SpWorkOrder::with(['creator', 'sessions.operator', 'sessions.productionEntries', 'sessions.rejectEntries', 'sessions.downtimeEntries', 'sessions.manpowerEntries'])->findOrFail($id);

        $firstPiece = FirstPieceInspection::where('part_number', $workOrder->part_number)
            ->whereDate('date', $workOrder->planned_date)
            ->orderBy('id', 'desc')
            ->first();

        if (!$firstPiece) {
            $firstPiece = FirstPieceInspection::where('part_number', $workOrder->part_number)
                ->orderBy('id', 'desc')
                ->first();
        }

        return view('sp_work_orders.show', compact('workOrder', 'firstPiece'));
    }

    public function edit($id)
    {
        $workOrder = SpWorkOrder::findOrFail($id);

        if ($workOrder->status !== 'planned') {
            return redirect()->route('sp-work-orders.show', $id)
                ->with('error', 'Only planned Work Orders can be edited.');
        }

        $lines = array_values(config('mes.sp_lines', []));
        $processes = ['Assembly', 'Painting', 'Buffing', 'Amplas', 'Trimming', 'Printing', 'Packing', 'Rework', 'Repair'];

        return view('sp_work_orders.edit', compact('workOrder', 'lines', 'processes'));
    }

    public function update(Request $request, $id)
    {
        $workOrder = SpWorkOrder::findOrFail($id);

        if ($workOrder->status !== 'planned') {
            return redirect()->route('sp-work-orders.show', $id)
                ->with('error', 'Only planned Work Orders can be updated.');
        }

        $validated = $request->validate([
            'planned_date' => 'required|date',
            'unit_line' => 'required|string',
            'shift' => 'required|string',
            'process_prod' => 'required|string',
            'part_number' => 'required|string',
            'part_name' => 'required|string',
            'model' => 'nullable|string',
            'customer' => 'required|string',
            'target_qty' => 'required|integer|min:1',
        ]);

        $workOrder->update($validated);

        return redirect()->route('sp-work-orders.show', $workOrder->id)
            ->with('success', "Work Order {$workOrder->wo_number} updated successfully.");
    }

    public function destroy($id)
    {
        $workOrder = SpWorkOrder::findOrFail($id);

        if ($workOrder->status !== 'planned') {
            return redirect()->back()
                ->with('error', 'Cannot delete Work Orders that are already in progress or completed.');
        }

        $workOrder->delete();

        return redirect()->route('sp-work-orders.index')
            ->with('success', 'Work Order deleted successfully.');
    }
}
