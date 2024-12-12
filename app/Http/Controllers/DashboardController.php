<?php

namespace App\Http\Controllers;

use App\Events\ParentDataUpdated;
use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_BillOfMaterialParent;
use App\Models\Production\PRD_MaterialLog;
use App\Models\Production\PRD_MouldingJob;
use App\Models\Production\PRD_MouldingUserLog;
use Illuminate\Http\Request;


class DashboardController extends Controller
{
    public function index()
    {
        $parents = PRD_BillOfMaterialParent::get();
        $childs = PRD_BillOfMaterialChild::get();
        $materialLogs = PRD_MaterialLog::get();
        $mouldingUserLogs = PRD_MouldingUserLog::get();
        $user = auth()->user();
        $totalPendingChildren = $childs->filter(function ($child) {
            return $child->action_type !== 'stockfinish' &&
                   ($child->action_type !== 'buyfinish' || $child->status !== 'Finished');
        })->count();
        $completedItems = $childs->filter(
            fn($child) => in_array($child->action_type, ['stockfinish']) || $child->status === 'Finished'
        )->count();
        $overallCompletionPercentage = $parents->map(function ($parent) use ($childs, $materialLogs) {
            $totalChildren = $childs->where('parent_id', $parent->id)->count();
            $finishedChildren = 0;

            foreach ($childs->where('parent_id', $parent->id) as $child) {
                $processCount = $materialLogs->where('child_id', $child->id)->count();
                $finishedCount = $materialLogs
                    ->where('child_id', $child->id)
                    ->filter(fn($log) => $log->status == 2)
                    ->count();

                if (($child->action_type === 'buyfinish' && $child->status === 'Finished') || $child->action_type === 'stockfinish') {
                    $finishedChildren++;
                } elseif ($processCount > 0 && $finishedCount === $processCount) {
                    $finishedChildren++;
                }
            }

            return $totalChildren > 0 ? ($finishedChildren / $totalChildren) * 100 : 0;
        })->average();

        if($user->role->name === 'ADMIN'){
            return view('dashboards.dashboard-admin', compact('parents', 'childs', 'materialLogs', 'mouldingUserLogs', 'completedItems', 'overallCompletionPercentage', 'totalPendingChildren'));
        } elseif($user->role->name === 'WORKSHOP') {
            return view('dashboards.dashboard-workshop', compact('user'));
        } else {
            return view('dashboard', compact('user'));
        }
    }

    // public function updateData()
    // {
    //     $parents = PRD_BillOfMaterialParent::all();
    //     $childs = PRD_BillOfMaterialChild::all();
    //     $materialLogs = PRD_MaterialLog::all();
    //     $mouldingUserLogs = PRD_MouldingUserLog::all();

    //     event(new ParentDataUpdated($parents, $childs, $materialLogs, $mouldingUserLogs));
    // }
}
