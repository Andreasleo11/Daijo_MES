<?php

namespace App\Services\Inventory;

/**
 * Represents the health classification of a finished-goods inventory item.
 */
enum StockStatus: string
{
    case Healthy  = 'healthy';
    case AtRisk   = 'at_risk';
    case Critical = 'critical';

    private const AT_RISK_MULTIPLIER = 1.5;

    public static function fromStock(?int $stock, ?int $safetyStock): self
    {
        $stock       = $stock       ?? 0;
        $safetyStock = $safetyStock ?? 0;

        if ($safetyStock <= 0) {
            return self::Healthy;
        }

        if ($stock < $safetyStock) {
            return self::Critical;
        }

        if ($stock < $safetyStock * self::AT_RISK_MULTIPLIER) {
            return self::AtRisk;
        }

        return self::Healthy;
    }

    public function label(): string
    {
        return match ($this) {
            self::Healthy  => 'Healthy',
            self::AtRisk   => 'At Risk',
            self::Critical => 'Critical',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::Healthy  => 'bg-green-100 text-green-800',
            self::AtRisk   => 'bg-yellow-100 text-yellow-800',
            self::Critical => 'bg-red-100 text-red-800',
        };
    }
}
