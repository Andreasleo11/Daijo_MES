<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterItemPhoto;

class DeliveryVerificationController extends Controller
{
    // Tampilkan halaman form scan
    public function index()
    {
        return view('delivery-verification.index');
    }

    // Proses scan / submit barcode
    public function check(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string'
        ]);

        $itemPhoto = MasterItemPhoto::where('item_code', $request->item_code)->first();
        
        return view('delivery-verification.index', [
            'item' => $itemPhoto,
            'item_code' => $request->item_code
        ]);
    }
}
