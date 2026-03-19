<?php

namespace App\Services;

use App\Models\CommissionSetting;
use App\Models\CommissionRule;

class CommissionService
{

    public static function getCommissionDetails($model, $amount = null)
    {
        $type = class_basename($model);

        $query = CommissionRule::where('status', 1);

        $query->where(function ($q) use ($model, $type) {

            // ITEM
            if ($type == 'Item') {
                $q->where(function ($q2) use ($model) {
                    $q2->where('type', 'item')
                       ->where('item_id', $model->id);
                });
            }

            // SUBCATEGORY
            if ($type == 'ItemSubcategory') {
                $q->orWhere(function ($q2) use ($model) {
                    $q2->where('type', 'subcategory')
                       ->where('subcategory_id', $model->id);
                });
            }

            // CATEGORY
            if ($type == 'ItemCategory') {
                $q->orWhere(function ($q2) use ($model) {
                    $q2->where('type', 'category')
                       ->where('category_id', $model->id);
                });
            }

            // GLOBAL
            $q->orWhere('type', 'global');
        });

        // 🔥 Amount filter
        if ($amount !== null) {
            $query->where(function ($q) use ($amount) {
                $q->whereNull('min_amount')
                  ->orWhere('min_amount', '<=', $amount);
            });

            $query->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')
                  ->orWhere('max_amount', '>=', $amount);
            });
        }

        // 🔥 Highest priority rule
        $rule = $query->orderByDesc('priority')->first();

        if ($rule) {
            return [
                'value' => $rule->commission_percent,
                'type'  => $rule->commission_type ?? 'percentage',
                'source'=> 'Rule'
            ];
        }

        // ===========================
        // 🔽 FALLBACK LOGIC
        // ===========================

        // ITEM
        if ($type == 'Item') {

            if ($model->commission_percent) {
                return [
                    'value' => $model->commission_percent,
                    'type'  => $model->commission_type ?? 'percentage',
                    'source'=> 'Item'
                ];
            }

            if ($model->subcategory && $model->subcategory->commission_percent) {
                return [
                    'value' => $model->subcategory->commission_percent,
                    'type'  => $model->subcategory->commission_type ?? 'percentage',
                    'source'=> 'Subcategory'
                ];
            }

            if ($model->category && $model->category->commission_percent) {
                return [
                    'value' => $model->category->commission_percent,
                    'type'  => $model->category->commission_type ?? 'percentage',
                    'source'=> 'Category'
                ];
            }
        }

        // SUBCATEGORY
        if ($type == 'ItemSubcategory') {

            if ($model->commission_percent) {
                return [
                    'value' => $model->commission_percent,
                    'type'  => $model->commission_type ?? 'percentage',
                    'source'=> 'Subcategory'
                ];
            }

            if ($model->category && $model->category->commission_percent) {
                return [
                    'value' => $model->category->commission_percent,
                    'type'  => $model->category->commission_type ?? 'percentage',
                    'source'=> 'Category'
                ];
            }
        }

        // CATEGORY
        if ($type == 'ItemCategory') {

            if ($model->commission_percent) {
                return [
                    'value' => $model->commission_percent,
                    'type'  => $model->commission_type ?? 'percentage',
                    'source'=> 'Category'
                ];
            }
        }

        // GLOBAL
        $global = CommissionSetting::first();

        return [
            'value' => $global ? $global->commission_percent : 0,
            'type'  => $global->commission_type ?? 'percentage',
            'source'=> 'Global'
        ];
    }


    /**
     * 🔥 Final Commission Calculation (IMPORTANT)
     */
    public static function calculateCommission($amount, $commission)
    {
        if (!$commission) return 0;

        $type  = $commission['type'] ?? 'percentage';
        $value = $commission['value'] ?? 0;

        // ✅ Percentage
        if ($type === 'percentage') {
            return ($amount * $value) / 100;
        }

        // ✅ Fixed
        if ($type === 'fixed') {
            return $value;
        }

        return 0;
    }


    /**
     * 🔥 Shortcut (percent only - backward compatible)
     */
    public static function getCommissionPercent($model, $amount = null)
    {
        $data = self::getCommissionDetails($model, $amount);
        return $data['value'] ?? 0;
    }
}