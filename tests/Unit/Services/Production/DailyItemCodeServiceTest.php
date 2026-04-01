<?php

namespace Tests\Unit\Services\Production;

use Tests\TestCase;
use App\Services\Production\DailyItemCodeService;

class DailyItemCodeServiceTest extends TestCase
{
    private DailyItemCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DailyItemCodeService();
    }

    public function test_transform_username_removes_leading_zeros()
    {
        $this->assertEquals('7A', $this->service->transformUsername('007A'));
        $this->assertEquals('12B', $this->service->transformUsername('012B'));
        $this->assertEquals('A', $this->service->transformUsername('A'));
        $this->assertEquals('Machine1', $this->service->transformUsername('Machine1'));
    }

    public function test_validate_shifts_sequence_detects_overlapping_shifts()
    {
        $validatedData = [
            'shifts' => [1, 2],
            'start_dates' => [1 => ['2023-10-01'], 2 => ['2023-10-01']],
            'end_dates' => [1 => ['2023-10-01'], 2 => ['2023-10-01']],
            'start_times' => [1 => ['08:00:00'], 2 => ['07:00:00']],
            'end_times' => [1 => ['16:00:00'], 2 => ['15:00:00']],
        ];

        $error = $this->service->validateShiftsSequence($validatedData);

        $this->assertNotNull($error);
        $this->assertEquals('start_times.2', $error['field']);
    }

    public function test_validate_shifts_sequence_allows_valid_shifts()
    {
        $validatedData = [
            'shifts' => [1, 2],
            'start_dates' => [1 => ['2023-10-01'], 2 => ['2023-10-01']],
            'end_dates' => [1 => ['2023-10-01'], 2 => ['2023-10-02']],
            'start_times' => [1 => ['08:00:00'], 2 => ['16:01:00']],
            'end_times' => [1 => ['16:00:00'], 2 => ['00:00:00']],
        ];

        $error = $this->service->validateShiftsSequence($validatedData);

        $this->assertNull($error);
    }
}
