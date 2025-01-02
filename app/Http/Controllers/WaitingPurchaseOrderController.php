<?php

namespace App\Http\Controllers;

use App\Models\WaitingPurchaseOrder;
use Illuminate\Http\Request;

class WaitingPurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = WaitingPurchaseOrder::all();
        return view('waiting_purchase_orders.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('waiting_purchase_orders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mold_name' => 'required|string|max:255',
            'capture_photo' => 'required|file|mimes:jpg,jpeg,png,gif|max:4096',
            'process' => 'required|string|max:255',
            'price' => 'required|string',
            'quotation_no' => 'required|string|max:255',
            'remark' => 'nullable|string',
        ]);

        // Handle file upload
        if ($request->hasFile('capture_photo')) {
            $file = $request->file('capture_photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads', $fileName); // Save file to storage/app/public/uploads
        }

        // Convert price to numeric format
        $price = str_replace(',', '', $request->input('price'));

        // Create the record
        WaitingPurchaseOrder::create([
            'mold_name' => $request->mold_name,
            'capture_photo_path' => $fileName,
            'process' => $request->process,
            'price' => $price,
            'quotation_no' => $request->quotation_no,
            'remark' => $request->remark,
        ]);

        return redirect()->route('waiting_purchase_orders.index')->with('success', 'Purchase Order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $waitingPurchaseOrder = WaitingPurchaseOrder::find($id);
        return view('waiting_purchase_orders.show', compact('waitingPurchaseOrder'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $waitingPurchaseOrder = WaitingPurchaseOrder::find($id);
        return view('waiting_purchase_orders.edit', compact('waitingPurchaseOrder'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'mold_name' => 'required|string|max:255',
            'capture_photo' => 'nullable|file|mimes:jpg,jpeg,png,gif|max:4096', // Optional for updates
            'process' => 'required|string|max:255',
            'price' => 'required|string',
            'quotation_no' => 'required|string|max:255',
            'remark' => 'nullable|string',
        ]);

        // Find the existing record
        $waitingPurchaseOrder = WaitingPurchaseOrder::findOrFail($id);

        // Handle file upload
        if ($request->hasFile('capture_photo')) {
            $file = $request->file('capture_photo');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/uploads', $fileName); // Save file to storage/app/public/uploads
            $waitingPurchaseOrder->capture_photo_path = $fileName; // Update file path in the record
        }

        // Convert price to numeric format
        $price = str_replace(',', '', $request->input('price'));

        // Update the record
        $waitingPurchaseOrder->update([
            'mold_name' => $request->mold_name,
            'process' => $request->process,
            'price' => $price,
            'quotation_no' => $request->quotation_no,
            'remark' => $request->remark,
            'capture_photo_path' => $waitingPurchaseOrder->capture_photo_path, // Retain old file if no new file uploaded
        ]);

        return redirect()->route('waiting_purchase_orders.index')->with('success', 'Purchase Order updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $waitingPurchaseOrder = WaitingPurchaseOrder::find($id);
        $waitingPurchaseOrder->delete();

        return redirect()->route('waiting_purchase_orders.index')->with('success', 'Purchase Order deleted successfully.');
    }
}
