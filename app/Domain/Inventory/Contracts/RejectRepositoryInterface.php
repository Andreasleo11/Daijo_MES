<?php

namespace App\Domain\Inventory\Contracts;

/**
 * Contract for accessing SAP reject/defect stock data.
 */
interface RejectRepositoryInterface
{
    /**
     * Return a map of item_no → in_stock for fast O(1) lookup
     * when enriching FG inventory rows.
     *
     * @return array<string, int|float>
     */
    public function getKeyedByItemNo(): array;
}
