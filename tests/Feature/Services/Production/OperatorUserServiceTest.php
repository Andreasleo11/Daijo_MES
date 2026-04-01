<?php

namespace Tests\Feature\Services\Production;

use Tests\TestCase;
use App\Models\OperatorUser;
use App\Services\Production\OperatorUserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OperatorUserServiceTest extends TestCase
{
    use DatabaseTransactions;

    private OperatorUserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OperatorUserService();

        OperatorUser::insert([
            'name' => 'John Doe',
            'password' => 'secret123',
            'profile_picture' => 'john.jpg',
            'position' => 'Operator',
            'department' => 'Production'
        ]);
    }

    public function test_get_users_with_qr_codes_generates_base64()
    {
        $result = $this->service->getUsersWithQrCodes();

        $john = collect($result)->firstWhere('name', 'John Doe');
        $this->assertNotNull($john);
        $this->assertEquals('John Doe', $john['name']);
        
        // Assert QR code is a base64 encoded PNG string
        $this->assertStringStartsWith('data:image/png;base64,', $john['qrCode']);
    }

    public function test_get_users_with_id_card_data_mocks_api_and_returns_data()
    {
        Http::fake([
            '192.168.6.75/JPayroll/thirdparty/ext/*' => Http::response([
                'data' => [
                    [
                        'Name' => 'John Doe',
                        'NIK' => 'NIK-JD-001'
                    ]
                ]
            ], 200)
        ]);

        $result = $this->service->getUsersWithIdCardData();

        $john = collect($result)->firstWhere('name', 'John Doe');
        $this->assertNotNull($john);
        $this->assertEquals('John Doe', $john['name']);
        $this->assertEquals('NIK-JD-001', $john['nik']);
        $this->assertEquals('john.jpg', $john['photo']);
        $this->assertEquals('Operator', $john['role']);
        $this->assertStringStartsWith('data:image/png;base64,', $john['qrCode']);
    }
}
