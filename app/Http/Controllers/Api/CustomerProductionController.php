<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyItemCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerProductionController extends Controller
{
    public function index()
    {
        $dailyItems = DailyItemCode::query()
            ->whereDate('schedule_date', Carbon::today())
            ->whereHas('hourlyRemarks')
            ->with([
                'user:id,name',
                'hourlyRemarks'
            ])
            ->get();

        $data = $dailyItems
            ->groupBy('item_code')
            ->map(function ($items, $itemCode) {
                $totalShots = (int)$items->sum(function ($item) {
                    return $item->hourlyRemarks->sum('actual_production') + $item->hourlyRemarks->sum('NG');
                });
                $totalNg = (int)$items->sum(function ($item) {
                    return $item->hourlyRemarks->sum('NG');
                });

                return [
                    'item_code' => $itemCode,
                    'planned_quantity' => (int)$items->sum('quantity'),
                    'total_shots' => $totalShots,
                    'total_ng' => $totalNg,
                    'machines' => $items
                        ->map(function ($item) {
                            $actualQty = (int)$item->hourlyRemarks->sum('actual_production');
                            $ngQty = (int)$item->hourlyRemarks->sum('NG');

                            return [
                                'daily_item_code_id' => $item->id,
                                'machine_name' => $item->user->name ?? '-',
                                'shift' => $item->shift,
                                'schedule_date' => $item->schedule_date,
                                'planned_quantity' => $item->quantity,
                                'actual_quantity' => $actualQty,
                                'final_quantity' => $actualQty + $ngQty,
                                'start_time' => $item->start_time,
                                'end_time' => $item->end_time,
                                'remark' => $item->remark,
                                'total_shots' => $actualQty + $ngQty,
                                'total_ng' => $ngQty,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'date' => Carbon::today()->format('Y-m-d'),
            'data' => $data,
        ]);
    }

    public function range(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        $dailyItems = DailyItemCode::query()
            ->whereBetween('schedule_date', [
                $request->start_date,
                $request->end_date
            ])
            ->whereHas('hourlyRemarks')
            ->with([
                'user:id,name',
                'hourlyRemarks'
            ])
            ->get();

        $data = $dailyItems
            ->groupBy('item_code')
            ->map(function ($items, $itemCode) {
                $totalShots = (int)$items->sum(function ($item) {
                    return $item->hourlyRemarks->sum('actual_production') + $item->hourlyRemarks->sum('NG');
                });
                $totalNg = (int)$items->sum(function ($item) {
                    return $item->hourlyRemarks->sum('NG');
                });

                return [
                    'item_code' => $itemCode,
                    'planned_quantity' => (int)$items->sum('quantity'),
                    'total_shots' => $totalShots,
                    'total_ng' => $totalNg,
                    'machines' => $items
                        ->map(function ($item) {
                            $actualQty = (int)$item->hourlyRemarks->sum('actual_production');
                            $ngQty = (int)$item->hourlyRemarks->sum('NG');

                            return [
                                'daily_item_code_id' => $item->id,
                                'machine_name' => $item->user->name ?? '-',
                                'shift' => $item->shift,
                                'schedule_date' => $item->schedule_date,
                                'planned_quantity' => $item->quantity,
                                'actual_quantity' => $actualQty,
                                'final_quantity' => $actualQty + $ngQty,
                                'start_time' => $item->start_time,
                                'end_time' => $item->end_time,
                                'remark' => $item->remark,
                                'total_shots' => $actualQty + $ngQty,
                                'total_ng' => $ngQty,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'data' => $data,
        ]);
    }
}