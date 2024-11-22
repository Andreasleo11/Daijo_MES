<?php

namespace App\Http\Controllers\Production;

use Milon\Barcode\Facades\DNS1DFacade as DNS1D;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Production\PRD_BillOfMaterialParent;
use App\Models\Production\PRD_BillOfMaterialChild;
use App\Models\Production\PRD_MaterialLog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class BillOfMaterialController extends Controller
{
    public function index()
    {
        $bomParents = PRD_BillOfMaterialParent::all();
        // dd($datas);
        return view('production.bom.index', compact('bomParents'));
    }

    public function create()
    {
        return view('production.bom.create');
    }

    public function destroy($id)
    {
        // Find the parent BOM record
        $parent = PRD_BillOfMaterialParent::findOrFail($id);

        // Delete all associated child records
        $parent->child()->delete(); // Assuming you have a relationship defined in the model

        // Delete the parent record
        $parent->delete();

        return redirect()->route('production.bom.index')->with('success', 'BOM and its children deleted successfully.');
    }

    public function show($id)
    {
        // Find the BOM parent by ID
        $bomParent = PRD_BillOfMaterialParent::with('child', 'child.materialProcess')->findOrFail($id);
        // dd($bomParent);
        $user = auth()->user();
        // dd($user);
        // Pass the data to the view
        return view('production.bom.detail', compact('bomParent','user'));
    }

    public function update(Request $request, $id)
    {
        $bomParent = PRD_BillOfMaterialParent::findOrFail($id);
        $bomParent->update($request->only(['item_code', 'item_description', 'type']));
        
        return redirect()->route('production.bom.show', $id)->with('success', 'BOM Parent updated successfully.');
    }

    public function updateChild(Request $request, $id)
    {
        // Validate input data
        $request->validate([
            'item_code' => 'required|string|max:255',
            'item_description' => 'required|string|max:255',
            'quantity' => 'required|numeric',
            'measure' => 'required|string|max:50',
        ]);

        // Find the child item by ID
        $childItem = PRD_BillOfMaterialChild::findOrFail($id);

        // Update the child item fields
        $childItem->item_code = $request->item_code;
        $childItem->item_description = $request->item_description;
        $childItem->quantity = $request->quantity;
        $childItem->measure = $request->measure;
        $childItem->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Child item updated successfully!');
    }

    public function destroyChild($id)
    {
        // Find the child item by its ID
        $childItem = PRD_BillOfMaterialChild::find($id);

        // Check if the item exists
        if ($childItem) {
            $childItem->delete(); // Delete the item
            return redirect()->back()->with('success', 'Child item deleted successfully.');
        }

        return redirect()->back()->with('error', 'Child item not found.');
    }
        
 

    public function store(Request $request)
    {
        // dd($request->all());
        // Validate common fields
        $validated = $request->validate([
            'item_code' => 'required|string',
            'item_description' => 'required|string',
            'type' => 'required|string|in:production,moulding',
            'excel_file' => 'nullable|file',
        ]);
        // dd($validated);

        // Create the parent BOM record
        $parent = PRD_BillOfMaterialParent::create([
            'item_code' => $validated['item_code'],
            'item_description' => $validated['item_description'],
            'type' => $validated['type'],
        ]);
        
        // Check if an Excel file is uploaded
        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');

            // Load the Excel file
            $spreadsheet = IOFactory::load($file->getPathName());
            $sheet = $spreadsheet->getActiveSheet();

            // Get the highest row and column numbers referenced in the sheet
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Loop through the rows, starting from the second row (since we skip the first row)
            for ($row = 2; $row <= $highestRow; $row++) {
                // Read cells in the row, excluding the first column (A)
                $childItemCode = $sheet->getCell(Coordinate::stringFromColumnIndex(2) . $row)->getValue(); // Column B
                $childItemDescription = $sheet->getCell(Coordinate::stringFromColumnIndex(3) . $row)->getValue(); // Column C
                $quantity = $sheet->getCell(Coordinate::stringFromColumnIndex(4) . $row)->getValue(); // Column D
                $measure = $sheet->getCell(Coordinate::stringFromColumnIndex(5) . $row)->getValue(); // Column E

                // Skip empty rows
                if (empty($childItemCode) || empty($childItemDescription)) {
                    continue;
                }
                // Create the child record
                PRD_BillOfMaterialChild::create([
                    'parent_id' => $parent->id,
                    'item_code' => $childItemCode,
                    'item_description' => $childItemDescription,
                    'quantity' => $quantity,
                    'measure' => $measure,
                ]);
            }
        } else {
            // Validate manual input fields for child items
            $validated = $request->validate([
                'child_item_code' => 'required|array|min:1',
                'child_item_code.*' => 'required|string',
                'child_item_description' => 'required|array|min:1',
                'child_item_description.*' => 'required|string',
                'quantity' => 'required|array|min:1',
                'quantity.*' => 'required|numeric',
                'measure' => 'required|array|min:1',
                'measure.*' => 'required|string',
            ]);

            // Loop through manually inputted child items and create records
            foreach ($validated['child_item_code'] as $key => $child_item_code) {
                PRD_BillOfMaterialChild::create([
                    'parent_id' => $parent->id,
                    'item_code' => $child_item_code,
                    'item_description' => $validated['child_item_description'][$key],
                    'quantity' => $validated['quantity'][$key],
                    'measure' => $validated['measure'][$key],
                ]);
            }
        }

        // Redirect back or to the BOM index page
        return redirect()->route('production.bom.index')->with('success', 'BOM created successfully');
    }

    public function storeChild(Request $request, PRD_BillOfMaterialParent $bomParent)
    {
        // dd($bomParent);
        $request->validate([
            'child.*.item_code' => 'required|string',
            'child.*.item_description' => 'required|string',
            'child.*.quantity' => 'required|numeric',
            'child.*.measure' => 'required|string',
        ]);

        foreach ($request->child as $childData) {
            // Add the parent_id to the child data before creating the child record
            $childData['parent_id'] = $bomParent->id;
    
            // Create the child record
            $bomParent->child()->create($childData);
        }

        return redirect()->route('production.bom.show', $bomParent)->with('success', 'Child items added successfully!');
    }

    public function uploadChildItems(Request $request, $bomParentId)
    {
        $bomParent = PRD_BillOfMaterialParent::findOrFail($bomParentId);

        // Check if the file was uploaded
        if ($request->hasFile('excel_file')) {
            $file = $request->file('excel_file');

            // Load the Excel file
            $spreadsheet = IOFactory::load($file->getPathName());
            $sheet = $spreadsheet->getActiveSheet();

            // Get the highest row and column numbers referenced in the sheet
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();

            // Loop through the rows, starting from the second row (since we skip the first row)
            for ($row = 2; $row <= $highestRow; $row++) {
                // Read cells in the row, excluding the first column (A)
                $childItemCode = $sheet->getCell(Coordinate::stringFromColumnIndex(2) . $row)->getValue(); // Column B
                $childItemDescription = $sheet->getCell(Coordinate::stringFromColumnIndex(3) . $row)->getValue(); // Column C
                $quantity = $sheet->getCell(Coordinate::stringFromColumnIndex(4) . $row)->getValue(); // Column D
                $measure = $sheet->getCell(Coordinate::stringFromColumnIndex(5) . $row)->getValue(); // Column E

                // Skip empty rows
                if (empty($childItemCode) || empty($childItemDescription)) {
                    continue;
                }

                // Create the child record
                PRD_BillOfMaterialChild::create([
                    'parent_id' => $bomParent->id,
                    'item_code' => $childItemCode,
                    'item_description' => $childItemDescription,
                    'quantity' => $quantity,
                    'measure' => $measure,
                ]);
            }

            return redirect()->route('production.bom.show', $bomParent->id)
                            ->with('success', 'Child items uploaded successfully.');
        }

        return redirect()->route('production.bom.show', $bomParent->id)
                        ->with('error', 'No file selected.');
    }

    public function assignType(Request $request, $childId)
    {
        $child = PRD_BillOfMaterialChild::findOrFail($childId);
        $child->action_type = $request->input('action_type');
        $child->save();

        return redirect()->back()->with('success', 'Action type assigned successfully.');
    }

    public function updateStatusChild($id)
    {
        // Find the child item by ID
        $child = PRD_BillOfMaterialChild::findOrFail($id);
        
        // Update the status to "Available"
        $child->status = 'Available';
        $child->save();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Item status updated to Available.');
    }

    public function assignProcess(Request $request, $id)
    {
         // Retrieve the processes from the request
        $processes = $request->input('all_process');

        // Validate if processes are provided
        if (empty($processes)) {
            return redirect()->back()->with('error', 'No processes were selected.');
        }

        // Loop through each process and insert it into the PRD_MaterialLog table
        foreach ($processes as $process) {
            PRD_MaterialLog::create([
                'child_id' => $id,           // The child ID passed to the method
                'process_name' => $process,   // The process name from the form
                'scan_in' => null,            // Set default value or leave as null
                'scan_out' => null,           // Set default value or leave as null
                // Status will use the default value of 0 in the database
            ]);
        }

        return redirect()->back()->with('success', 'Processes assigned successfully.');
    }

    public function materialDetail($id)
    {
        // Retrieve the child and its related material processes
        $child = PRD_BillOfMaterialChild::with('materialProcess','parent', 'materialProcess.workers')->findOrFail($id);
        // Generate barcode (item_code and id in the barcode)
        // dd($child);
        $barcodeData = $child->item_code . '~' . $child->id; // Item Code and ID separated by ~
        
        // Generate the barcode PNG content
        $barcodePNG = DNS1D::getBarcodePNG($barcodeData, 'C128');
        
        // Define the file name and path
        $fileName = 'barcode_' . $child->item_code . '_' . $child->id . '.png';
        $filePath = 'public/barcode/' . $fileName;

        // Save the barcode image to storage
        Storage::put($filePath, base64_decode($barcodePNG));

        // Get the URL for the barcode image
        $barcodeUrl = asset('storage/barcode/' . $fileName);

        // Pass the data to the view
        return view('production.bom.child_detail', compact('child', 'barcodeUrl'));
    }

}
