<?php

namespace App\Services\Production;

use App\Models\OperatorUser;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Http;

class OperatorUserService
{
    /**
     * Get all operator users with their generated QR codes.
     */
    public function getUsersWithQrCodes(): array
    {
        $users = OperatorUser::all();
        $qrCodes = [];

        foreach ($users as $user) {
            $qrCodes[] = [
                'name' => $user->name,
                'qrCode' => $this->generateQrCodeBase64($user->name, $user->password),
            ];
        }

        return $qrCodes;
    }

    /**
     * Get all operator users including QR codes and NIK from external API (JPayroll).
     */
    public function getUsersWithIdCardData(): array
    {
        // Hit JPayroll API to get employee data
        $response = Http::withHeaders([
             'Authorization' => 'Basic QVBJPUV4VCtEQCFqMDpEQCFqMEBKcDR5cjAxMQ=='
        ])->post('http://192.168.6.75/JPayroll/thirdparty/ext/API_View_Master_Employee.php', [
             'CompanyArea' => '10000'
        ]);

        $data = $response->json();
        $users = OperatorUser::all();
        $qrCodes = [];

        foreach ($users as $user) {
            $nik = $this->getNikByName($user->name, $data['data'] ?? []);

            $qrCodes[] = [
                'name' => $user->name,
                'qrCode' => $this->generateQrCodeBase64($user->name, $user->password),
                'photo' => $user->profile_picture,
                'role' => $user->position,
                'department' => $user->department,
                'nik' => $nik ?? 'NIK Not Found',
            ];
        }

        return $qrCodes;
    }

    /**
     * Generate a Base64 encoded PNG of the QR Code for a given user.
     */
    private function generateQrCodeBase64(string $name, string $password): string
    {
        $barcodeData = $name . "\t" . $password;

        $qrCode = new QrCode(
            data: $barcodeData,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 100,
            margin: 5
        );

        $writer = new PngWriter();
        $qrCodeResult = $writer->write($qrCode);

        $qrCodeImage = $qrCodeResult->getString();
        $qrcoded = base64_encode($qrCodeImage);

        return 'data:image/png;base64,' . $qrcoded;
    }

    /**
     * Helper to find NIK by Name from API response
     */
    private function getNikByName(string $name, array $employeeData): ?string
    {
        foreach ($employeeData as $employee) {
            if (isset($employee['Name']) && strtolower(trim($employee['Name'])) === strtolower(trim($name))) {
                return $employee['NIK'];
            }
        }
        return null;
    }
}
