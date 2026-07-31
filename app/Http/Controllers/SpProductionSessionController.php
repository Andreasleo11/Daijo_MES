<?php

namespace App\Http\Controllers;

use App\Models\SpDowntimeEntry;
use App\Models\SpInputEntry;
use App\Models\SpProductionEntry;
use App\Models\SpProductionSession;
use App\Models\SpRejectEntry;
use App\Models\SpReworkEntry;
use App\Models\SpWorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpProductionSessionController extends Controller
{
    public function start(Request $request, $workOrderId)
    {
        $workOrder = SpWorkOrder::findOrFail($workOrderId);

        if (in_array($workOrder->status, ['completed', 'cancelled'])) {
            return redirect()->route('sp-work-orders.show', $workOrderId)
                ->with('error', 'Cannot start production on a completed or cancelled Work Order.');
        }

        // Check if active running session already exists for this WO
        $existingSession = SpProductionSession::where('work_order_id', $workOrderId)
            ->where('status', 'running')
            ->first();

        if ($existingSession) {
            return redirect()->route('app.sp-sessions.show', $existingSession->id)
                ->with('info', 'Resuming active production session.');
        }

        $session = SpProductionSession::create([
            'work_order_id' => $workOrder->id,
            'operator_id' => auth()->id(),
            'unit_line' => $workOrder->unit_line,
            'shift' => $workOrder->shift,
            'status' => 'running',
            'started_at' => now(),
        ]);

        $workOrder->update(['status' => 'in_progress']);

        return redirect()->route('app.sp-sessions.show', $session->id)
            ->with('success', "Production started for Work Order {$workOrder->wo_number}.");
    }

    public function show($id)
    {
        $session = SpProductionSession::with([
            'workOrder',
            'operator',
            'productionEntries' => fn($q) => $q->orderBy('recorded_at', 'desc'),
            'rejectEntries' => fn($q) => $q->orderBy('created_at', 'desc'),
            'reworkEntries' => fn($q) => $q->orderBy('created_at', 'desc'),
            'downtimeEntries' => fn($q) => $q->orderBy('created_at', 'desc'),
            'inputEntries' => fn($q) => $q->orderBy('created_at', 'desc'),
        ])->findOrFail($id);

        $defectTypes = [
            'Flash',
            'Scratch',
            'Short Shot',
            'Sink Mark',
            'Burn Mark',
            'Assembly Defect',
            'Printing Defect',
            'Color Mis-match',
            'Contamination',
            'Others',
        ];

        $downtimeReasons = [
            'Waiting for Material',
            'Machine Breakdown',
            'Quality Hold',
            'Change Model',
            'No Operator',
            'Meeting',
            'Power Failure',
            'Maintenance',
        ];

        return view('sp_production.record', compact('session', 'defectTypes', 'downtimeReasons'));
    }

    public function addProduction(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Cannot log production on a finished session.'], 422);
        }

        $validated = $request->validate([
            'good_qty' => 'required|integer|min:0',
            'reject_qty' => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $entry = $session->productionEntries()->create([
            'good_qty' => $validated['good_qty'],
            'reject_qty' => $validated['reject_qty'] ?? 0,
            'recorded_at' => now(),
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $session->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Production recorded successfully.',
                'entry' => $entry,
                'totals' => [
                    'good' => $session->total_good,
                    'reject' => $session->total_reject,
                    'input' => $session->total_input,
                    'yield' => $session->yield,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Production recorded successfully.');
    }

    public function addReject(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Session is not running.'], 422);
        }

        $validated = $request->validate([
            'defect_type' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'cause' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $entry = $session->rejectEntries()->create($validated);
        $session->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Defect recorded successfully.',
                'entry' => $entry,
                'totals' => [
                    'good' => $session->total_good,
                    'reject' => $session->total_reject,
                    'input' => $session->total_input,
                    'yield' => $session->yield,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Defect recorded successfully.');
    }

    public function addRework(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Session is not running.'], 422);
        }

        $validated = $request->validate([
            'input_qty' => 'required|integer|min:1',
            'recovered_qty' => 'required|integer|min:0',
            'scrapped_qty' => 'required|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        $entry = $session->reworkEntries()->create($validated);
        $session->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rework recorded successfully.',
                'entry' => $entry,
                'totals' => [
                    'rework_in' => $session->total_rework_in,
                    'rework_recovered' => $session->total_rework_recovered,
                    'scrap' => $session->total_scrap,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Rework recorded successfully.');
    }

    public function addDowntime(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Session is not running.'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'resume_time' => 'required|date_format:H:i',
            'remarks' => 'nullable|string',
        ]);

        $today = now()->format('Y-m-d');
        $startDT = Carbon::parse("{$today} {$validated['start_time']}");
        $resumeDT = Carbon::parse("{$today} {$validated['resume_time']}");

        if ($resumeDT->lt($startDT)) {
            $resumeDT->addDay();
        }

        $duration = (int) $startDT->diffInMinutes($resumeDT);

        $entry = $session->downtimeEntries()->create([
            'reason' => $validated['reason'],
            'start_time' => $startDT,
            'resume_time' => $resumeDT,
            'duration_minutes' => $duration,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Downtime recorded successfully.',
                'entry' => $entry,
                'totals' => [
                    'downtime_minutes' => $session->downtimeEntries()->sum('duration_minutes'),
                    'downtime_count' => $session->downtimeEntries()->count()
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Downtime recorded successfully.');
    }

    public function addInput(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Session is not running.'], 422);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'source' => 'nullable|string',
            'pallet_number' => 'nullable|string',
            'remarks' => 'nullable|string',
        ]);

        $entry = $session->inputEntries()->create([
            'quantity' => $validated['quantity'],
            'source' => $validated['source'] ?? 'manual',
            'pallet_number' => $validated['pallet_number'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $session->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Input quantity recorded successfully.',
                'entry' => $entry,
                'totals' => [
                    'input' => $session->total_input,
                    'yield' => $session->yield,
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Input quantity recorded successfully.');
    }

    public function finish(Request $request, $id)
    {
        $session = SpProductionSession::with('workOrder')->findOrFail($id);

        if ($session->status === 'completed') {
            return redirect()->route('sp-work-orders.show', $session->work_order_id)
                ->with('info', 'Session is already completed.');
        }

        $session->update([
            'status' => 'completed',
            'finished_at' => now(),
            'remarks' => $request->input('remarks'),
        ]);

        $session->workOrder->update(['status' => 'completed']);

        return redirect()->route('sp-work-orders.show', $session->work_order_id)
            ->with('success', 'Production session completed successfully.');
    }

    public function approve(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'completed') {
            return back()->with('error', 'Only completed sessions can be approved.');
        }

        $session->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Session approved successfully by supervisor.');
    }
}
