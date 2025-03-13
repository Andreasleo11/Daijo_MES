<?php

namespace App\Http\Controllers;

use App\Models\OperatorUser;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

class OperatorUserController extends Controller
{
    public function showQr()
    {
        // Retrieve all operator users
        $users = OperatorUser::all();

        // Initialize an empty array for QR codes
        $qrCodes = [];

        // Loop through each user to generate QR codes
        foreach ($users as $user) {
            $name = $user->name;
            $password = $user->password;

            // Prepare the QR code content (name and password separated by tab)
            $barcodeData = $name . "\t" . $password;

            // Create a new QR code with specified parameters
            $qrCode = new QrCode(
                data: $barcodeData,
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 100,  // Adjust the size as needed
                margin: 5    // Adjust the margin as needed
            );

            // Generate the QR code using the PngWriter
            $writer = new PngWriter();
            $qrCodeResult = $writer->write($qrCode);

            // Get the PNG image as a string
            $qrCodeImage = $qrCodeResult->getString();

            // Base64 encode the image to embed in HTML
            $qrcoded = base64_encode($qrCodeImage);

            // Store the name and the generated QR code data URL
            $qrCodes[] = [
                'name' => $name,
                'qrCode' => 'data:image/png;base64,' . $qrcoded,
            ];
        }

        // Pass the QR codes and user names to the view
        return view('barcode.qr_operator', ['qrCodes' => $qrCodes]);
    }
}
