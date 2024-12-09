<?php

namespace App\Http\Controllers;

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


        if($user->role->name === 'ADMIN'){
            return view('dashboards.dashboard-admin', compact('parents', 'childs', 'materialLogs', 'mouldingUserLogs'));
        } elseif($user->role->name === 'WORKSHOP') {
            return view('dashboard.dashboard-workshop', compact('user'));
        } else {
            return view('dashboard', compact('user'));
        }
    }
}
