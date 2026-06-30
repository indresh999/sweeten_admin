<?php

namespace App\Services;

use App\Models\Item;

class CommissionService
{
    /**
     * Resolve the best commission rule for an item.
     * Priority: item > subcategory > category > global default (5%)
     */
    public static function getCommissionDetails(Item $item, float $price): array
    {
        // Item-level override
        if (!empty($item->commission_type) && !empty($item->commission_value)) {
            return ['type' => $item->commission_type, 'value' => (float) $item->commission_value, 'source' => 'item'];
        }

        // Subcategory
        if ($item->subcategory) {
            $sub = $item->subcategory;
            if (!empty($sub->commission_type)) {
                return ['type' => $sub->commission_type, 'value' => (float) ($sub->commission_percent ?? 0), 'source' => 'subcategory'];
            }
        }

        // Category
        if ($item->category) {
            $cat = $item->category;
            if (!empty($cat->commission_type)) {
                return ['type' => $cat->commission_type, 'value' => (float) ($cat->commission_percent ?? 0), 'source' => 'category'];
            }
        }

        // Global default
        return ['type' => 'percent', 'value' => 5.0, 'source' => 'default'];
    }

    public static function calculateCommission(float $price, array $commission): float
    {
        if ($commission['type'] === 'flat') {
            return (float) $commission['value'];
        }
        return round($price * $commission['value'] / 100, 2);
    }
}
