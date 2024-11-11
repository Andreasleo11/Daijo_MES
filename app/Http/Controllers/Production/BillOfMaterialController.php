<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Production\PRD_BillOfMaterialParent;
use App\Models\Production\PRD_BillOfMaterialChild;

class BillOfMaterialController extends Controller
{
    public function index()
    {
        $datas = PRD_BillOfMaterialParent::get();
        // dd($datas);
        return view('production.bom.index');
    }
    
    public function create()
    {
        return view('production.bom.create');
    }

    public function store(Request $request)
    {
        // Validation for incoming data
        $validated = $request->validate([
            'item_code' => 'required|string',
            'item_description' => 'required|string',
            'type' => 'required|string|in:production,moulding', // Only allow these types
            'child_item_code' => 'required|array|min:1',
            'child_item_code.*' => 'required|string',
            'child_item_description' => 'required|array|min:1',
            'child_item_description.*' => 'required|string',
            'quantity' => 'required|array|min:1',
            'quantity.*' => 'required|numeric',
            'measure' => 'required|array|min:1',
            'measure.*' => 'required|string',
        ]);

        // Create the parent BOM record
        $parent = PRD_BillOfMaterialParent::create([
            'item_code' => $validated['item_code'],
            'item_description' => $validated['item_description'],
            'type' => $validated['type'],
        ]);

        // Loop through child items and create records
        foreach ($validated['child_item_code'] as $key => $child_item_code) {
            PRD_BillOfMaterialChild::create([
                'parent_id' => $parent->id, // Associate with the parent BOM
                'item_code' => $child_item_code,
                'item_description' => $validated['child_item_description'][$key],
                'quantity' => $validated['quantity'][$key],
                'measure' => $validated['measure'][$key],
            ]);
        }

        // Redirect back or to the BOM index page
        return redirect()->route('production.bom.index')->with('success', 'BOM created successfully');
    }
}
