<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SecondDailyProcess;
use App\Models\MasterAllItem;

class SecondDailyController extends Controller
{
    public function index()
    {
        $datas = SecondDailyProcess::get();
        return view('second.index', compact('datas'));
    }

    public function create()
    {
        $schedule = SecondDailyProcess::get();

        return view('second.create');
    }

    public function searchItems(Request $request)
    {
        $query = $request->input('query');
        $items = MasterAllItem::where('item_code', 'like', "%{$query}%")
                ->orWhere('item_description', 'like', "%{$query}%")
                ->limit(50) // Limit results to improve performance
                ->get();

        return response()->json($items);
    }

    public function getItemDescription(Request $request)
    {
        $itemCode = $request->query('item_code');

        // Validate that the item code was provided
        if (!$itemCode) {
            return response()->json(['error' => 'Item code is required'], 400);
        }

        // Retrieve the item from the database
        $item = MasterAllItem::where('item_code', $itemCode)->first();

        // Check if the item exists
        if (!$item) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Return the item description
        return response()->json(['item_description' => $item->item_description]);
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'plan_date' => 'required|date',
            'pic' => 'required|string',
            'shift' => 'required|integer',
            'line' => 'required|array',
            'item_code' => 'required|array',
            'item_description' => 'required|array',
            'quantity_hour' => 'required|array',
            'process_time' => 'required|array',
            'quantity_plan' => 'required|array',
            'customer' => 'required|array',
        ]);

        // Loop through each entry based on the number of items
        foreach ($data['line'] as $index => $line) {
            SecondDailyProcess::create([
                'plan_date' => $data['plan_date'],
                'pic' => $data['pic'],
                'shift' => $data['shift'],
                'line' => $line,
                'item_code' => $data['item_code'][$index],
                'item_description' => $data['item_description'][$index],
                'quantity_hour' => $data['quantity_hour'][$index],
                'process_time' => $data['process_time'][$index],
                'quantity_plan' => $data['quantity_plan'][$index],
                'customer' => $data['customer'][$index],
            ]);
        }

        return redirect()->route('second.daily.process.index')
            ->with('success', 'Entries have been added successfully.');
    }
}
