<?php

namespace App\Http\Controllers;

use App\Models\MasterListItem;
use Illuminate\Http\Request;
use Milon\Barcode\DNS1D;

class InitialBarcodeController extends Controller
{
    public function index()
    {
        $tipeMesins = MasterListItem::distinct()->pluck('tipe_mesin');
        // dd($tipeMesins);

        return view('barcode.index', compact('tipeMesins'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'tipe_mesin' => 'required',
        ]);

        // Fetch items with the selected tipe_mesin
        $items = MasterListItem::where('tipe_mesin', $request->tipe_mesin)->get();
        $labelCount = 1;

        return view('barcode.generate', compact('items', 'labelCount'));
    }

    public function manualgenerate()
    {
        return view('barcode.generatemanualbarcode');
    }

    public function generateBarcode(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string',
            'quantity' => 'required|integer',
            'warehouse' => 'required|string',
            'label' => 'required|integer',
        ]);

        $item_code = $request->input('item_code');
        $quantity = $request->input('quantity');
        $warehouse = $request->input('warehouse');
        $labelCount = $request->input('label');

        $barcodes = [];

        $barcodeGenerator = new DNS1D;

        for ($i = 1; $i <= $labelCount; $i++) {
            $barcodeData = "{$item_code}\t{$quantity}\t{$warehouse}\t{$i}";
            $barcode = $barcodeGenerator->getBarcodeHTML($barcodeData, 'C128');

            $barcodes[] = [
                'barcode' => $barcode,
                'item_code' => $item_code,
                'quantity' => $quantity,
                'label' => $i,
            ];
        }

        return view('barcode.barcode_result', compact('barcodes'));
    }
}
