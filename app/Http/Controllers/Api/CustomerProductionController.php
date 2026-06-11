<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyItemCode;
use Carbon\Carbon;

class CustomerProductionController extends Controller
{
    public function index()
    {
        $dailyItems = DailyItemCode::query()

            ->whereDate(
                'schedule_date',
                Carbon::today()
            )

            ->whereHas('hourlyRemarks')

            ->with([
                'user:id,name',
                'hourlyRemarks'
            ])

            ->get();

        $data = $dailyItems

            ->groupBy('item_code')

            ->map(function ($items, $itemCode) {

                return [

                    'item_code' => $itemCode,

                    'quantity' => (int)
                        $items->sum('quantity'),

                    'actual_quantity' => (int)
                        $items->sum('actual_quantity'),

                    'final_quantity' => (int)
                        $items->sum('final_quantity'),

                    'total_shots' => (int)
                        $items->sum(function ($item) {
                            return $item
                                ->hourlyRemarks
                                ->sum('actual_production');
                        }),

                    'total_ng' => (int)
                        $items->sum(function ($item) {
                            return $item
                                ->hourlyRemarks
                                ->sum('NG');
                        }),

                    'machines' => $items

                        ->map(function ($item) {

                            return [

                                'daily_item_code_id' => $item->id,

                                'machine_name' =>
                                    $item->user->name ?? '-',

                                'shift' =>
                                    $item->shift,

                                'schedule_date' =>
                                    $item->schedule_date,

                                'quantity' =>
                                    $item->quantity,

                                'actual_quantity' =>
                                    $item->actual_quantity,

                                'final_quantity' =>
                                    $item->final_quantity,

                                'start_time' =>
                                    $item->start_time,

                                'end_time' =>
                                    $item->end_time,

                                'remark' =>
                                    $item->remark,

                                'total_shots' =>
                                    (int)$item
                                        ->hourlyRemarks
                                        ->sum('actual_production'),

                                'total_ng' =>
                                    (int)$item
                                        ->hourlyRemarks
                                        ->sum('NG'),
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

                return [

                    'item_code' => $itemCode,

                    'quantity' => (int)
                        $items->sum('quantity'),

                    'actual_quantity' => (int)
                        $items->sum('actual_quantity'),

                    'final_quantity' => (int)
                        $items->sum('final_quantity'),

                    'total_shots' => (int)
                        $items->sum(function ($item) {
                            return $item
                                ->hourlyRemarks
                                ->sum('actual_production');
                        }),

                    'total_ng' => (int)
                        $items->sum(function ($item) {
                            return $item
                                ->hourlyRemarks
                                ->sum('NG');
                        }),

                    'machines' => $items

                        ->map(function ($item) {

                            return [

                                'daily_item_code_id' => $item->id,

                                'machine_name' =>
                                    $item->user->name ?? '-',

                                'schedule_date' =>
                                    $item->schedule_date,

                                'shift' =>
                                    $item->shift,

                                'quantity' =>
                                    $item->quantity,

                                'actual_quantity' =>
                                    $item->actual_quantity,

                                'final_quantity' =>
                                    $item->final_quantity,

                                'start_time' =>
                                    $item->start_time,

                                'end_time' =>
                                    $item->end_time,

                                'remark' =>
                                    $item->remark,

                                'total_shots' =>
                                    (int)$item
                                        ->hourlyRemarks
                                        ->sum('actual_production'),

                                'total_ng' =>
                                    (int)$item
                                        ->hourlyRemarks
                                        ->sum('NG'),
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