<?php

namespace App\Http\Controllers;

use App\Models\SpDowntimeEntry;
use App\Models\SpInputEntry;
use App\Models\SpProductionEntry;
use App\Models\SpProductionSession;
use App\Models\SpRejectEntry;
use App\Models\SpReworkEntry;
use App\Models\SpSessionMaterial;
use App\Models\SpWorkOrder;
use App\Models\FirstPieceInspection;
use App\Services\SecondProcessReportSyncBridge;
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

        $isBypassed = false;
        $bypassReason = null;

        if ($request->boolean('bypass_qc')) {
            $request->validate([
                'bypass_reason' => 'required|string|min:3|max:255',
            ]);
            $isBypassed = true;
            $bypassReason = $request->input('bypass_reason');
        } else {
            // Validate QC-Approved First Piece Inspection before starting session (latest for part)
            $firstPiece = FirstPieceInspection::latestForPart($workOrder->part_number)->first();

            if (!$firstPiece || !$firstPiece->isApproved()) {
                $reason = !$firstPiece 
                    ? "No First Piece Inspection record found." 
                    : ($firstPiece->overall_judgement !== 'OK' ? "Overall judgement is {$firstPiece->overall_judgement}." : "Pending QC signature.");

                return redirect()->back()
                    ->with('error', "Cannot start production: First Piece Inspection for Part {$workOrder->part_number} is not approved by QC ({$reason}).");
            }
        }

        $session = SpProductionSession::create([
            'work_order_id' => $workOrder->id,
            'operator_id' => auth()->id(),
            'unit_line' => $workOrder->unit_line,
            'shift' => $this->getCurrentShift(Carbon::now('Asia/Jakarta')),
            'status' => 'running',
            'started_at' => now(),
            'is_qc_bypassed' => $isBypassed,
            'qc_bypass_reason' => $bypassReason,
            'qc_bypassed_at' => $isBypassed ? now() : null,
            'qc_bypassed_by' => $isBypassed ? auth()->id() : null,
        ]);

        $workOrder->update(['status' => 'in_progress']);

        $message = $isBypassed 
            ? "Production started with Emergency QC Bypass for Work Order {$workOrder->wo_number}."
            : "Production started for Work Order {$workOrder->wo_number}.";

        return redirect()->route('app.sp-sessions.show', $session->id)
            ->with('success', $message);
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
            'manpowerEntries' => fn($q) => $q->orderBy('created_at', 'desc'),
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
            'good_qty' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ]);

        $newOutput = $validated['good_qty'];

        $currentOutput = $session->total_good + $session->total_reject;
        if (($currentOutput + $newOutput) > $session->total_input) {
            $availableWip = max(0, $session->total_input - $currentOutput);
            return response()->json([
                'error' => "Total output cannot exceed available Input WIP ({$availableWip} Pcs available out of {$session->total_input} Pcs total). Please log Input WIP first."
            ], 422);
        }

        $entry = $session->productionEntries()->create([
            'good_qty' => $validated['good_qty'],
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
                    'downtime_minutes' => $session->downtimeEntries->sum('duration_minutes') ?? 0,
                    'rework_in' => $session->total_rework_in,
                    'rework_recovered' => $session->total_rework_recovered,
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

        $currentOutput = $session->total_good + $session->total_reject;
        if (($currentOutput + $validated['quantity']) > $session->total_input) {
            $availableWip = max(0, $session->total_input - $currentOutput);
            return response()->json([
                'error' => "Total output cannot exceed available Input WIP ({$availableWip} Pcs available out of {$session->total_input} Pcs total). Please log Input WIP first."
            ], 422);
        }

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
                    'downtime_minutes' => $session->downtimeEntries->sum('duration_minutes') ?? 0,
                    'rework_in' => $session->total_rework_in,
                    'rework_recovered' => $session->total_rework_recovered,
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

        if ($validated['input_qty'] > $session->total_reject) {
            return response()->json([
                'error' => "Cannot rework more than total logged defects ({$session->total_reject} Pcs)."
            ], 422);
        }

        if (($validated['recovered_qty'] + $validated['scrapped_qty']) > $validated['input_qty']) {
            return response()->json([
                'error' => 'Recovered + Scrapped quantity cannot exceed Rework Input quantity.'
            ], 422);
        }

        $entry = $session->reworkEntries()->create($validated);
        $session->recalculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Rework recorded successfully.',
                'entry' => $entry,
                'totals' => [
                    'input' => $session->total_input,
                    'good' => $session->total_good,
                    'reject' => $session->total_reject,
                    'yield' => $session->yield,
                    'rework_in' => $session->total_rework_in,
                    'rework_recovered' => $session->total_rework_recovered,
                    'scrap' => $session->total_scrap,
                    'downtime_minutes' => $session->downtimeEntries()->sum('duration_minutes'),
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

        $sessionDate = $session->started_at
            ? $session->started_at->setTimezone('Asia/Jakarta')->format('Y-m-d')
            : Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $startDT = Carbon::createFromFormat('Y-m-d H:i', "{$sessionDate} {$validated['start_time']}", 'Asia/Jakarta')->setTimezone('UTC');
        $resumeDT = Carbon::createFromFormat('Y-m-d H:i', "{$sessionDate} {$validated['resume_time']}", 'Asia/Jakarta')->setTimezone('UTC');

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

    public function addManpower(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Session is not running.'], 422);
        }

        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'operator_name' => 'required|string|max:255',
            'employee_no' => 'nullable|string|max:100',
            'role' => 'required|string|max:100',
        ]);

        $entry = $session->manpowerEntries()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team member added successfully.',
                'entry' => $entry
            ]);
        }

        return redirect()->back()->with('success', 'Team member added successfully.');
    }

    public function removeManpower(Request $request, $id, $manpowerId)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Session is not running.'], 422);
        }

        $manpower = $session->manpowerEntries()->findOrFail($manpowerId);
        $manpower->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Team member removed successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Team member removed successfully.');
    }

    public function finish(Request $request, $id)
    {
        $session = SpProductionSession::with('workOrder')->findOrFail($id);

        if ($session->status === 'completed') {
            return redirect()->route('app.sp-sessions.closeout', $session->id)
                ->with('info', 'Session is already completed.');
        }

        $session->update([
            'status' => 'completed',
            'finished_at' => now(),
            'remarks' => $request->input('remarks'),
        ]);

        if ($session->workOrder) {
            $wo = $session->workOrder;
            $cumulativeGood = $wo->sessions()->sum('total_good');

            if ($cumulativeGood >= $wo->target_qty) {
                $wo->update(['status' => 'completed']);
            } else {
                $wo->update(['status' => 'planned']);
            }
        }

        return redirect()->route('app.sp-sessions.closeout', $session->id)
            ->with('success', 'Session completed. Please fill in the close-out details.');
    }

    public function closeout($id)
    {
        $session = SpProductionSession::with([
            'workOrder',
            'operator',
            'materials',
            'downtimeEntries',
            'manpowerEntries',
        ])->findOrFail($id);

        if ($session->status !== 'completed') {
            return redirect()->route('app.sp-sessions.show', $session->id)
                ->with('error', 'Session must be completed before close-out.');
        }

        $troubleCategories = ['Man', 'Mesin', 'Part', 'PPS', 'Lingkungan'];

        return view('sp_production.closeout', compact('session', 'troubleCategories'));
    }

    public function submitCloseout(Request $request, $id)
    {
        $session = SpProductionSession::with('workOrder')->findOrFail($id);

        $validated = $request->validate([
            'production_notes' => 'nullable|string',
            'ng_remarks' => 'nullable|string',
            'absent_employees' => 'nullable|string',
            'next_production_schedule' => 'nullable|array',
            'output_destination' => 'nullable|string',
            'remarks' => 'nullable|string',
            // Materials
            'materials' => 'nullable|array',
            'materials.*.type' => 'required|string|in:paint,part',
            'materials.*.item_name' => 'required|string',
            'materials.*.lot_number' => 'nullable|string',
            'materials.*.visco' => 'nullable|string',
            'materials.*.mixing_ratio' => 'nullable|string',
            'materials.*.qty' => 'nullable|numeric',
            'materials.*.uom' => 'nullable|string',
            // Downtime enrichment
            'troubles' => 'nullable|array',
            'troubles.*.downtime_id' => 'nullable|integer',
            'troubles.*.category' => 'nullable|string',
            'troubles.*.countermeasure' => 'nullable|string',
        ]);

        DB::transaction(function () use ($session, $validated) {
            $session->update([
                'production_notes' => $validated['production_notes'] ?? null,
                'ng_remarks' => $validated['ng_remarks'] ?? null,
                'absent_employees' => $validated['absent_employees'] ?? null,
                'next_production_schedule' => $validated['next_production_schedule'] ?? null,
                'output_destination' => $validated['output_destination'] ?? null,
                'remarks' => $validated['remarks'] ?? $session->remarks,
            ]);

            // Save materials
            $session->materials()->delete();
            foreach (($validated['materials'] ?? []) as $mat) {
                if (!empty($mat['item_name'])) {
                    $session->materials()->create($mat);
                }
            }

            // Enrich downtime entries with category + countermeasure
            foreach (($validated['troubles'] ?? []) as $trouble) {
                if (!empty($trouble['downtime_id'])) {
                    SpDowntimeEntry::where('id', $trouble['downtime_id'])
                        ->where('session_id', $session->id)
                        ->update([
                            'category' => $trouble['category'] ?? null,
                            'countermeasure' => $trouble['countermeasure'] ?? null,
                        ]);
                }
            }
        });

        $spLines = config('mes.sp_lines', []);
        $lineSlug = array_search($session->unit_line, $spLines)
            ?: \Illuminate\Support\Str::slug($session->unit_line);

        return redirect()->route('sp-sessions.line-gateway', [
            'lineSlug' => $lineSlug,
            'date' => $session->started_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'shift' => $session->shift ?? 1,
        ])->with('success', 'Session close-out completed successfully.');
    }

    /**
     * Bookmarkable per-line gateway.
     * Shows all assigned WOs for this line with per-WO session state.
     */
    public function lineGateway(Request $request, string $lineSlug)
    {
        // Resolve slug -> display name from config
        $spLines = config('mes.sp_lines', []);
        $line = $spLines[$lineSlug] ?? null;

        if (!$line) {
            // Fallback check if user passed exact line name (e.g. Line A)
            $foundSlug = array_search($lineSlug, $spLines);
            if ($foundSlug !== false) {
                return redirect()->route('sp-sessions.line-gateway', ['lineSlug' => $foundSlug]);
            }
            abort(404, "Unknown line: {$lineSlug}");
        }

        // Active date & shift filter (locked to today)
        $now = Carbon::now('Asia/Jakarta');
        $date = $now->format('Y-m-d');
        $currentShift = $this->getCurrentShift($now);

        $shift = (int) $request->query('shift', $currentShift);
        $selectedShift = $request->query('shift', (string) $shift);
        $currentShiftConfig = config("mes.sp_shifts.{$shift}", config("mes.shifts.{$shift}", []));

        // Work Orders for this line (active planned or in_progress only; draft and completed excluded)
        $workOrdersQuery = SpWorkOrder::with(['sessions' => fn($q) => $q->orderByDesc('started_at')])
            ->where('unit_line', $line)
            ->whereIn('status', ['planned', 'in_progress'])
            ->where(function ($q) use ($date) {
                $q->whereDate('planned_date', $date)
                    ->orWhereHas('sessions', fn($s) => $s->where('status', 'running'));
            });

        $workOrders = $workOrdersQuery->orderByDesc('id')->get();

        // Fetch First Piece Inspections for line work orders (latest per part, date-agnostic)
        $partNumbers = $workOrders->pluck('part_number')->filter()->unique();
        $firstPieceMap = FirstPieceInspection::whereIn('part_number', $partNumbers)
            ->latest('id')
            ->get()
            ->unique('part_number')
            ->keyBy('part_number');

        // Fetch quick bypass reasons (default factory presets + historical DB reasons)
        $defaultBypassPresets = collect([
            'Urgent Delivery Run - Pre-approved by Supervisor',
            'Supervisor Verbal Approval Given',
            'Repeat Order - Same Machine & Tooling Setup',
            'QC Team Walking Floor / Delayed Inspection',
        ]);

        $historicalBypassReasons = SpProductionSession::where('is_qc_bypassed', true)
            ->whereNotNull('qc_bypass_reason')
            ->distinct()
            ->pluck('qc_bypass_reason');

        $quickBypassReasons = $defaultBypassPresets->merge($historicalBypassReasons)->filter()->unique()->values();

        return view('sp_production.line_gateway', compact(
            'line', 'lineSlug', 'date', 'shift', 'selectedShift', 'currentShiftConfig', 'currentShift',
            'workOrders', 'firstPieceMap', 'spLines', 'quickBypassReasons'
        ));
    }

    private function getCurrentShift(Carbon $now)
    {
        $shifts = config('mes.sp_shifts', config('mes.shifts', []));
        $currentTime = $now->format('H:i');

        foreach ($shifts as $shiftId => $schedule) {
            $start = $schedule['start'];
            $end = $schedule['end'];

            if ($start > $end) {
                if ($currentTime >= $start || $currentTime <= $end) {
                    return $shiftId;
                }
            } else {
                if ($currentTime >= $start && $currentTime <= $end) {
                    return $shiftId;
                }
            }
        }
        return 1;
    }

    public function pause($id)
    {
        $session = SpProductionSession::findOrFail($id);

        if ($session->status !== 'running') {
            return response()->json(['error' => 'Only running sessions can be paused.'], 422);
        }

        if (!\Illuminate\Support\Facades\Schema::hasColumn('sp_production_sessions', 'paused_at')) {
            return response()->json(['error' => 'The database column "paused_at" is missing. Please run php artisan migrate on your server.'], 422);
        }

        $session->paused_at = now();
        $session->save();

        return response()->json([
            'success' => true,
            'message' => 'Line paused. Breakdown / Stop timer started.',
            'paused_at' => $session->paused_at->toIso8601String(),
        ]);
    }

    public function resume(Request $request, $id)
    {
        $session = SpProductionSession::findOrFail($id);

        if (!\Illuminate\Support\Facades\Schema::hasColumn('sp_production_sessions', 'paused_at')) {
            return response()->json(['error' => 'The database column "paused_at" is missing. Please run php artisan migrate on your server.'], 422);
        }

        if (!$session->paused_at) {
            return response()->json(['error' => 'Session is not currently paused.'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $pausedAt = $session->paused_at;
        $now = now();
        $durationMinutes = max(1, (int) round($pausedAt->diffInMinutes($now)));

        $downtime = $session->downtimeEntries()->create([
            'reason' => $validated['reason'],
            'start_time' => $pausedAt,
            'resume_time' => $now,
            'duration_minutes' => $durationMinutes,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        $session->paused_at = null;
        $session->save();
        $session->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => "Line resumed. Logged {$durationMinutes}m downtime for '{$downtime->reason}'.",
            'downtime' => $downtime,
            'totals' => $this->getSessionTotalsArray($session)
        ]);
    }

    public function deleteProductionEntry($sessionId, $entryId)
    {
        $session = SpProductionSession::findOrFail($sessionId);
        if ($session->status !== 'running') {
            return response()->json(['error' => 'Cannot delete entries on a finished session.'], 422);
        }

        $entry = $session->productionEntries()->findOrFail($entryId);
        $entry->delete();
        $session->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Production entry removed successfully.',
            'totals' => $this->getSessionTotalsArray($session)
        ]);
    }

    public function deleteRejectEntry($sessionId, $entryId)
    {
        $session = SpProductionSession::findOrFail($sessionId);
        if ($session->status !== 'running') {
            return response()->json(['error' => 'Cannot delete entries on a finished session.'], 422);
        }

        $entry = $session->rejectEntries()->findOrFail($entryId);
        $entry->delete();
        $session->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Defect entry removed successfully.',
            'totals' => $this->getSessionTotalsArray($session)
        ]);
    }

    public function deleteDowntimeEntry($sessionId, $entryId)
    {
        $session = SpProductionSession::findOrFail($sessionId);
        if ($session->status !== 'running') {
            return response()->json(['error' => 'Cannot delete entries on a finished session.'], 422);
        }

        $entry = $session->downtimeEntries()->findOrFail($entryId);
        $entry->delete();
        $session->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Downtime entry removed successfully.',
            'totals' => $this->getSessionTotalsArray($session)
        ]);
    }

    public function deleteReworkEntry($sessionId, $entryId)
    {
        $session = SpProductionSession::findOrFail($sessionId);
        if ($session->status !== 'running') {
            return response()->json(['error' => 'Cannot delete entries on a finished session.'], 422);
        }

        $entry = $session->reworkEntries()->findOrFail($entryId);
        $entry->delete();
        $session->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Rework entry removed successfully.',
            'totals' => $this->getSessionTotalsArray($session)
        ]);
    }

    public function deleteInputEntry($sessionId, $entryId)
    {
        $session = SpProductionSession::findOrFail($sessionId);
        if ($session->status !== 'running') {
            return response()->json(['error' => 'Cannot delete entries on a finished session.'], 422);
        }

        $entry = $session->inputEntries()->findOrFail($entryId);
        $entry->delete();
        $session->recalculateTotals();

        return response()->json([
            'success' => true,
            'message' => 'Input WIP entry removed successfully.',
            'totals' => $this->getSessionTotalsArray($session)
        ]);
    }

    private function getSessionTotalsArray(SpProductionSession $session): array
    {
        return [
            'good' => $session->total_good,
            'reject' => $session->total_reject,
            'input' => $session->total_input,
            'yield' => $session->yield,
            'downtime_minutes' => (int) ($session->downtimeEntries()->sum('duration_minutes') ?? 0),
            'rework_in' => $session->total_rework_in,
            'rework_recovered' => $session->total_rework_recovered,
        ];
    }
}
