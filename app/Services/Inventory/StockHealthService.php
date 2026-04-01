<?php

namespace App\Services\Inventory;

use App\Models\Delivery\sapInventoryFg;
use App\Models\Delivery\SapReject;
use App\Services\Inventory\DTOs\StockHealthFilterDTO;
use App\Services\Inventory\DTOs\StockHealthItemDTO;

class StockHealthService
{
    /**
     * Get stock health dashboard data (items, summary, owners, families)
     */
    public function getDashboardData(StockHealthFilterDTO $filter): array
    {
        // 1. Get reject map (O(1) lookup)
        $rejectMap = SapReject::query()
            ->select(['item_no', 'in_stock'])
            ->get()
            ->keyBy('item_no')
            ->map(fn($row) => $row->in_stock)
            ->all();

        // 2. Build Inventory FG Query
        $query = sapInventoryFg::query();

        if (!empty($filter->search)) {
            $term = $filter->search;
            $query->where(function ($q) use ($term) {
                $q->where('item_code', 'like', "%{$term}%")
                  ->orWhere('item_name', 'like', "%{$term}%");
            });
        }

        if (!empty($filter->processOwner)) {
            $query->where('process_owner', $filter->processOwner);
        }

        if (!empty($filter->family)) {
            $query->where('family', $filter->family);
        }

        // 3. Fetch Rows
        $fgRows = $query->select([
                'item_code',
                'item_name',
                'process_owner',
                'family',
                'stock',
                'safety_stock',
            ])
            ->orderBy('process_owner')
            ->orderBy('item_code')
            ->get();

        // 4. Map to DTOs
        $items = $fgRows->map(function ($row) use ($rejectMap): StockHealthItemDTO {
            $stock       = (int) ($row->stock        ?? 0);
            $safetyStock = (int) ($row->safety_stock ?? 0);
            $rejectStock = $rejectMap[$row->item_code] ?? 0;

            return new StockHealthItemDTO(
                itemCode:     $row->item_code    ?? '',
                itemName:     $row->item_name    ?? '',
                processOwner: $row->process_owner ?? '',
                family:       $row->family        ?? '',
                stock:        $stock,
                safetyStock:  $safetyStock,
                rejectStock:  $rejectStock,
                status:       StockStatus::fromStock($stock, $safetyStock),
            );
        })->values()->all();

        // 5. Build Summary
        $summary = $this->buildSummary($items);

        // 6. Get distinct filters
        $processOwners = sapInventoryFg::query()
            ->whereNotNull('process_owner')
            ->where('process_owner', '!=', '')
            ->distinct()
            ->orderBy('process_owner')
            ->pluck('process_owner')
            ->all();

        $families = sapInventoryFg::query()
            ->whereNotNull('family')
            ->where('family', '!=', '')
            ->distinct()
            ->orderBy('family')
            ->pluck('family')
            ->all();

        return [
            'items'         => $items,
            'summary'       => $summary,
            'processOwners' => $processOwners,
            'families'      => $families,
        ];
    }

    /**
     * @param  StockHealthItemDTO[]  $items
     * @return array{total: int, healthy: int, at_risk: int, critical: int}
     */
    private function buildSummary(array $items): array
    {
        $summary = ['total' => count($items), 'healthy' => 0, 'at_risk' => 0, 'critical' => 0];

        foreach ($items as $item) {
            match ($item->status) {
                StockStatus::Healthy  => $summary['healthy']++,
                StockStatus::AtRisk   => $summary['at_risk']++,
                StockStatus::Critical => $summary['critical']++,
            };
        }

        return $summary;
    }
}
