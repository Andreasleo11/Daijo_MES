<?php

namespace App\Services\Inventory\DTOs;

use App\Services\Inventory\StockStatus;

readonly class StockHealthItemDTO
{
    public function __construct(
        public readonly string      $itemCode,
        public readonly string      $itemName,
        public readonly string      $processOwner,
        public readonly string      $family,
        public readonly int         $stock,
        public readonly int         $safetyStock,
        public readonly int|float   $rejectStock,
        public readonly StockStatus $status,
    ) {}
}
