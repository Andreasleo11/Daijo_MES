<?php

namespace App\Http\Controllers;

use App\Models\OperatorUser;
use Illuminate\Http\Request;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

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

    public function index()
    {
        $users = OperatorUser::all();
        return view('operator.index', compact('users'));
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:operator_user,id',
            'profile_picture' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = OperatorUser::findOrFail($request->user_id);

        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profile_pictures', $filename, 'public');

            // Delete old profile picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Update user's profile picture
            $user->update(['profile_picture' => $path]);
        }

        return back()->with('success', 'Profile picture updated successfully.');
    }
}
