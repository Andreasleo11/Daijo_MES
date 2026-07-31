<?php

namespace App\Http\Controllers;

use App\Models\SpProductionSession;
use App\Services\SecondProcessReportSyncBridge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpProductionApprovalController extends Controller
{
    /**
     * Display a list of pending and completed session approvals.
     */
    public function index(Request $request)
    {
        // By default, show sessions that are 'completed' and not yet approved.
        // We can add a filter later to show already approved ones.
        $tab = $request->get('tab', 'pending');

        $query = SpProductionSession::with(['workOrder', 'operator'])
            ->where('status', 'completed');

        if ($tab === 'approved') {
            $query->whereNotNull('approved_at')->orderBy('approved_at', 'desc');
        } else {
            // Default to pending
            $query->whereNull('approved_at')->orderBy('finished_at', 'asc');
        }

        $sessions = $query->paginate(15);

        return view('sp_approvals.index', compact('sessions', 'tab'));
    }

    /**
     * Display the specified session for review.
     */
    public function show(SpProductionSession $session)
    {
        // Abort if not completed
        if ($session->status !== 'completed') {
            abort(404, 'Only completed sessions can be reviewed.');
        }

        // Load necessary relationships
        $session->load([
            'workOrder',
            'operator',
            'approvedBy',
            'productionEntries',
            'rejectEntries',
            'downtimeEntries',
            'reworkEntries',
            'inputEntries'
        ]);

        return view('sp_approvals.show', compact('session'));
    }

    /**
     * Approve the completed session and sync to legacy SecondProcessReport.
     */
    public function approve(Request $request, SpProductionSession $session, SecondProcessReportSyncBridge $bridge)
    {
        if ($session->status !== 'completed' || $session->approved_at !== null) {
            return redirect()->back()->with('error', 'Session cannot be approved.');
        }

        $session->update([
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Trigger Sync Bridge to legacy SecondProcessReport schema
        $bridge->syncSessionToLegacyReport($session);

        return redirect()->route('sp-approvals.index')->with('success', 'Production report approved & synced successfully.');
    }

    /**
     * Reject the completed session and return it for correction.
     */
    public function reject(Request $request, SpProductionSession $session, SecondProcessReportSyncBridge $bridge)
    {
        if ($session->status !== 'completed' || $session->approved_at !== null) {
            return redirect()->back()->with('error', 'Session cannot be returned.');
        }

        // Return to 'running' so operator can edit it again
        $session->update([
            'status' => 'running',
            'finished_at' => null, // Clear the finished time so it acts like it's active again
            'approved_by' => null,
            'approved_at' => null,
        ]);

        // Revert legacy sync status
        $bridge->handleSessionReversion($session);

        return redirect()->route('sp-approvals.index')->with('warning', 'Session returned to operator for correction.');
    }
}
