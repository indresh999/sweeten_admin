<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\ItemVariant;
use Illuminate\Database\Seeder;

class ItemVariantSeeder extends Seeder
{
    private array $variantSets = [
        // Cakes - 500g, 1kg, 2kg
        'cake' => [
            ['label' => '500g',  'multiplier' => 1.0],
            ['label' => '1 kg',  'multiplier' => 1.9],
            ['label' => '2 kg',  'multiplier' => 3.6],
        ],
        // Sweets - 250g, 500g, 1kg
        'sweet' => [
            ['label' => '250g',  'multiplier' => 0.55],
            ['label' => '500g',  'multiplier' => 1.0],
            ['label' => '1 kg',  'multiplier' => 1.9],
        ],
        // Namkeen / Sev / Bhujia - 250g, 500g, 1kg
        'namkeen' => [
            ['label' => '250g',  'multiplier' => 0.55],
            ['label' => '500g',  'multiplier' => 1.0],
            ['label' => '1 kg',  'multiplier' => 1.85],
        ],
        // Default - 1 piece, Half, Full
        'default' => [
            ['label' => 'Half',  'multiplier' => 0.6],
            ['label' => 'Full',  'multiplier' => 1.0],
            ['label' => 'Family', 'multiplier' => 1.8],
        ],
    ];

    private array $itemVariantMap = [
        'Pineapple Cake (500g)'   => 'cake',
        'Chocolate Truffle (1kg)' => 'cake',
        'Black Forest (500g)'     => 'cake',
        'Kaju Barfi'              => 'sweet',
        'Kaju Katli'              => 'sweet',
        'Gulab Jamun (12 pcs)'    => 'sweet',
        'Rasgulla (1 kg)'         => 'sweet',
        'Rasgkkkk'                => 'sweet',
        'Jalebi (500g)'           => 'sweet',
        'Aloo Bhujia (500g)'      => 'namkeen',
        'Mix Namkeen (500g)'      => 'namkeen',
        'Sev (500g)'              => 'namkeen',
    ];

    public function run(): void
    {
        ItemVariant::truncate();

        $items = Item::all();

        foreach ($items as $item) {
            $setType = $this->itemVariantMap[$item->item_name] ?? 'default';
            $variants = $this->variantSets[$setType];

            foreach ($variants as $idx => $v) {
                $price = round($item->price * $v['multiplier'], 0);
                $offer = $idx === 0 ? null : round($price * 0.92, 0); // ~8% discount on non-default

                ItemVariant::create([
                    'item_id'      => $item->id,
                    'label'        => $v['label'],
                    'price'        => $price,
                    'offer_price'  => $offer,
                    'is_default'   => $idx === 1, // middle = default
                    'status'       => 'active',
                ]);
            }

            $this->command->info("Item #{$item->id} ({$item->item_name}) -> " . count($variants) . " variants [{$setType}]");
        }

        $this->command->info("Done! Created " . ($items->count() * 3) . " variants for {$items->count()} items.");
    }
}
