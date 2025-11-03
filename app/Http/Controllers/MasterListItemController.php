<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Models\Delivery\sapLineProduction;

class MasterListItemController extends Controller
{
    public function index()
    {
        $users = User::whereBetween('id', [13, 62])->pluck('name', 'id');

        return view('master-list-item.index', compact('users'));
    }

    public function generateMachineList(Request $request)
    {
        $machineName = $request->machine_name;
        $machines = SapLineProduction::where('line_production', $machineName)->get();

        $qrcodes = [];
        $images = [];

        foreach ($machines as $machine) {
            // Generate QR code data
            $qrCodeData = $machine->item_code;

            $qrCode = new QrCode(
                data: $qrCodeData,
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: 100,
                margin: 5
            );

            // Create the QR code image with PngWriter
            $writer = new PngWriter();
            $qrCodeResult = $writer->write($qrCode);

            // Get the PNG image as a string and base64 encode it
            $qrcodes[$machine->item_code] = base64_encode($qrCodeResult->getString());

            // Get only the correct item photo (PNG format)
            $storagePath = storage_path('app/public/files/');
            $files = File::glob($storagePath . '*-' . $machine->item_code . '.png'); // Only PNG files

            // If a PNG file exists, store its path; otherwise, use a default image
            $images[$machine->item_code] = !empty($files)
                ? str_replace(storage_path('app/public/'), 'storage/', $files[0])
                : asset('storage/default-image.png'); // Default image if not found
        }

        return view('master-list-item.machine-list', compact('machines', 'qrcodes', 'machineName', 'images'));
    }
}
