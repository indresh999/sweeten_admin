<?php
$pdo = new PDO("mysql:host=72.62.242.223;dbname=backup;charset=utf8mb4","backup","SD3SsDWNKNtDScmn");

// Unsplash free images mapped by food type (400x300 crop)
$images = [
    // Indian Sweets (laddu, barfi, peda, halwa, etc.)
    'laddu'        => 'https://images.unsplash.com/photo-1666190466789-91f5d79ed228?w=400&h=300&fit=crop',
    'motichoor'    => 'https://images.unsplash.com/photo-1666190466789-91f5d79ed228?w=400&h=300&fit=crop',
    'kaju_katli'   => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop',
    'soan_papdi'   => 'https://images.unsplash.com/photo-1609183480237-ccf9872743e1?w=400&h=300&fit=crop',
    'halwa'        => 'https://images.unsplash.com/photo-1645177628172-a94c1f96e6db?w=400&h=300&fit=crop',
    'rasmalai'     => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop',
    'burfi'        => 'https://images.unsplash.com/photo-1609183480237-ccf9872743e1?w=400&h=300&fit=crop',
    'peda'         => 'https://images.unsplash.com/photo-1666190466789-91f5d79ed228?w=400&h=300&fit=crop',
    'milk_cake'    => 'https://images.unsplash.com/photo-1645177628172-a94c1f96e6db?w=400&h=300&fit=crop',
    'kalakand'     => 'https://images.unsplash.com/photo-1631452180519-c014fe946bc7?w=400&h=300&fit=crop',
    'mysore_pak'   => 'https://images.unsplash.com/photo-1609183480237-ccf9872743e1?w=400&h=300&fit=crop',
    'modak'        => 'https://images.unsplash.com/photo-1666190466789-91f5d79ed228?w=400&h=300&fit=crop',
    'dry_fruit_laddu' => 'https://images.unsplash.com/photo-1666190466789-91f5d79ed228?w=400&h=300&fit=crop',
    'coconut_barfi' => 'https://images.unsplash.com/photo-1609183480237-ccf9872743e1?w=400&h=300&fit=crop',

    // Bakery & Cake
    'cake'         => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=300&fit=crop',
    'cupcake'      => 'https://images.unsplash.com/photo-1576618148400-f54bed99fcfd?w=400&h=300&fit=crop',
    'cookies'      => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=300&fit=crop',
    'bread'        => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    'brownie'      => 'https://images.unsplash.com/photo-1564355808539-22fda35bed7e?w=400&h=300&fit=crop',
    'pastry'       => 'https://images.unsplash.com/photo-1509365390695-33aee754301f?w=400&h=300&fit=crop',
    'rusk'         => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',

    // Pizza
    'pizza'        => 'https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?w=400&h=300&fit=crop',
    'garlic_bread' => 'https://images.unsplash.com/photo-1549931319-a545753467c8?w=400&h=300&fit=crop',
    'lava_cake'    => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=400&h=300&fit=crop',

    // Beverages / Juice
    'orange_juice' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=400&h=300&fit=crop',
    'mango_lassi'  => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?w=400&h=300&fit=crop',
    'watermelon'   => 'https://images.unsplash.com/photo-1563114773-84221bd62daa?w=400&h=300&fit=crop',
    'cold_coffee'  => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?w=400&h=300&fit=crop',
    'milkshake'    => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400&h=300&fit=crop',
    'ice_cream'    => 'https://images.unsplash.com/photo-1497034825429-c343d7c6a68f?w=400&h=300&fit=crop',
    'chai'         => 'https://images.unsplash.com/photo-1564890369478-c89ca6d9cde9?w=400&h=300&fit=crop',
    'tea'          => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=300&fit=crop',
    'lemon_tea'    => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=300&fit=crop',
    'coffee'       => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400&h=300&fit=crop',
    'coconut_water'=> 'https://images.unsplash.com/photo-1536657464919-89a4963a44fd?w=400&h=300&fit=crop',

    // Snacks
    'samosa'       => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&h=300&fit=crop',
    'bhel_puri'    => 'https://images.unsplash.com/photo-1606491956689-2ea866880049?w=400&h=300&fit=crop',
    'dhokla'       => 'https://images.unsplash.com/photo-1630384060440-34e5f1b8c8a3?w=400&h=300&fit=crop',
    'kachori'      => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&h=300&fit=crop',
    'bread_pakora' => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&h=300&fit=crop',
    'puff'         => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
    'popcorn'      => 'https://images.unsplash.com/photo-1585735036851-4743c5828a90?w=400&h=300&fit=crop',
    'chakli'       => 'https://images.unsplash.com/photo-1606491956689-2ea866880049?w=400&h=300&fit=crop',
    'peanuts'      => 'https://images.unsplash.com/photo-1563589173315-91561a898ead?w=400&h=300&fit=crop',
    'mixture'      => 'https://images.unsplash.com/photo-1606491956689-2ea866880049?w=400&h=300&fit=crop',
    'cashews'      => 'https://images.unsplash.com/photo-1563589173315-91561a898ead?w=400&h=300&fit=crop',
    'almonds'      => 'https://images.unsplash.com/photo-1563589173315-91561a898ead?w=400&h=300&fit=crop',

    // Chips & Packaged
    'chips'        => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=300&fit=crop',
    'kurkure'      => 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400&h=300&fit=crop',
    'parle_g'      => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=400&h=300&fit=crop',

    // Dairy & Grocery
    'ghee'         => 'https://images.unsplash.com/photo-1631209121750-a9f656d28f46?w=400&h=300&fit=crop',
    'paneer'       => 'https://images.unsplash.com/photo-1631209121750-a9f656d28f46?w=400&h=300&fit=crop',

    // Indo-Chinese
    'noodles'      => 'https://images.unsplash.com/photo-1569718212165-3a8278d5f624?w=400&h=300&fit=crop',
    'fried_rice'   => 'https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400&h=300&fit=crop',
    'manchurian'   => 'https://images.unsplash.com/photo-1631209121750-a9f656d28f46?w=400&h=300&fit=crop',
    'chilli_paneer'=> 'https://images.unsplash.com/photo-1631209121750-a9f656d28f46?w=400&h=300&fit=crop',

    // South Indian
    'idli'         => 'https://images.unsplash.com/photo-1630383249896-424e482df921?w=400&h=300&fit=crop',
    'vada'         => 'https://images.unsplash.com/photo-1630383249896-424e482df921?w=400&h=300&fit=crop',
    'dosa'         => 'https://images.unsplash.com/photo-1630383249896-424e482df921?w=400&h=300&fit=crop',
    'uttapam'      => 'https://images.unsplash.com/photo-1630383249896-424e482df921?w=400&h=300&fit=crop',

    // Kulfi
    'kulfi'        => 'https://images.unsplash.com/photo-1570197571499-166b36435e9f?w=400&h=300&fit=crop',

    // Party Supplies
    'balloons'     => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=400&h=300&fit=crop',
    'candles'      => 'https://images.unsplash.com/photo-1558636508-e0db3814bd1d?w=400&h=300&fit=crop',
    'topper'       => 'https://images.unsplash.com/photo-1558636508-e0db3814bd1d?w=400&h=300&fit=crop',

    // Chicken
    'chicken'      => 'https://images.unsplash.com/photo-1601050690597-df0568f70950?w=400&h=300&fit=crop',
];

// Map item names to image keys
$nameToKey = [
    'Motichoor Laddu Box'        => 'laddu',
    'Kaju Katli (250g)'          => 'kaju_katli',
    'Soan Papdi (500g)'          => 'soan_papdi',
    'Badam Halwa'                => 'halwa',
    'Rasmalai (4 pcs)'           => 'rasmalai',
    'Bhel Puri Plate'            => 'bhel_puri',
    'Gulab Jamun (6 pcs)'        => 'laddu',
    'Besan Burfi'                => 'burfi',
    'Kesar Peda (12 pcs)'        => 'peda',
    'Milk Cake (500g)'           => 'milk_cake',
    'Kalakand (250g)'            => 'kalakand',
    'Mysore Pak (200g)'          => 'mysore_pak',
    'Dry Fruit Laddu Box'        => 'dry_fruit_laddu',
    'Modak (6 pcs)'              => 'modak',
    'Veg Samosa (4 pcs)'         => 'samosa',
    'Khaman Dhokla'              => 'dhokla',
    'Raj Kachori'                => 'kachori',
    'Chocolate Truffle Cake 1kg' => 'cake',
    'Red Velvet Cupcakes (6 pcs)' => 'cupcake',
    'Butter Cookies (200g)'      => 'cookies',
    'Multigrain Bread'           => 'bread',
    'Chocolate Brownie (4 pcs)'  => 'brownie',
    'Vanilla Pastry (2 pcs)'     => 'pastry',
    'Margherita Pizza 8"'        => 'pizza',
    'Farm Fresh Pizza 8"'        => 'pizza',
    'Garlic Breadsticks (6 pcs)' => 'garlic_bread',
    'Choco Lava Cake'            => 'lava_cake',
    'Fresh Orange Juice'         => 'orange_juice',
    'Mango Lassi'                => 'mango_lassi',
    'Watermelon Juice'           => 'watermelon',
    'Cold Coffee'                => 'cold_coffee',
    'Chocolate Milkshake'        => 'milkshake',
    'Mango Ice Cream'            => 'ice_cream',
    'Masala Chai'                => 'chai',
    'Sulaimani Chai'             => 'tea',
    'Iced Lemon Tea'             => 'lemon_tea',
    'Parle-G Biscuit Pack'       => 'parle_g',
    'Butter Popcorn'             => 'popcorn',
    'Aloo Bhujia (200g)'         => 'mixture',
    'Navratan Mixture (250g)'    => 'mixture',
    'Lays Classic Salted'        => 'chips',
    'Kurkure Masala Munch'       => 'kurkure',
    'Chakli (200g)'              => 'chakli',
    'Masala Peanuts (200g)'      => 'peanuts',
    'Roasted Salted Cashews (100g)' => 'cashews',
    'Roasted Almonds (100g)'     => 'almonds',
    'Tender Coconut Water'       => 'coconut_water',
    'A2 Desi Ghee (500ml)'       => 'ghee',
    'Fresh Paneer (250g)'        => 'paneer',
    'Veg Samosa (2 pcs)'         => 'samosa',
    'Bhel Puri'                  => 'bhel_puri',
    'Bread Pakora (2 pcs)'       => 'bread_pakora',
    'Veg Puff'                   => 'puff',
    'Chicken Samosa (4 pcs)'     => 'chicken',
    'Birthday Balloon Pack'      => 'balloons',
    'Birthday Candles (12 pcs)'  => 'candles',
    'Happy Birthday Cake Topper' => 'topper',
    'Hakka Noodles'              => 'noodles',
    'Schezwan Fried Rice'        => 'fried_rice',
    'Veg Manchurian'             => 'manchurian',
    'Chilli Paneer Dry'          => 'chilli_paneer',
    'Moong Dal Halwa'            => 'halwa',
    'Coconut Barfi'              => 'coconut_barfi',
    'Idli Sambar (4 pcs)'        => 'idli',
    'Medu Vada (3 pcs)'          => 'vada',
    'Rusk (200g)'                => 'rusk',
    'Masala Dosa'                => 'dosa',
    'Rava Dosa'                  => 'dosa',
    'Filter Coffee'              => 'coffee',
    'Onion Uttapam'              => 'uttapam',
    'Malai Kulfi'                => 'kulfi',
];

$stmt = $pdo->query("SELECT id, item_name FROM items WHERE status != 'deleted'");
$updated = 0;
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $key = $nameToKey[$row['item_name']] ?? null;
    if (!$key || !isset($images[$key])) {
        echo "  SKIP: {$row['item_name']} (no image mapping)\n";
        continue;
    }
    $url = $images[$key];
    $json = json_encode([$url]);
    $upd = $pdo->prepare("UPDATE items SET images = ? WHERE id = ?");
    $upd->execute([$json, $row['id']]);
    echo "  ✓ #{$row['id']} {$row['item_name']} → $key\n";
    $updated++;
}

echo "\nDone! Updated $updated items with images.\n";
