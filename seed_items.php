<?php
$pdo = new PDO('mysql:host=72.62.242.223;dbname=backup;charset=utf8mb4','backup','SD3SsDWNKNtDScmn');

$count = $pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
echo "Existing items: $count\n";

// Shop → Category mapping with items
$shopItems = [
    // ── Sweets (cat 1) ──
    9 => [ // Amaravati Biryani House
        ['category_id'=>1,'subcategory_id'=>1,'name'=>'Motichoor Laddu Box','desc'=>'Freshly made motichoor laddus, soft and melt-in-mouth','price'=>180,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>2,'name'=>'Kaju Katli (250g)','desc'=>'Premium kaju katli made with real cashews','price'=>350,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>9,'name'=>'Soan Papdi (500g)','desc'=>'Flaky soan papdi in traditional style','price'=>160,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>7,'name'=>'Badam Halwa','desc'=>'Rich almond halwa with ghee and saffron','price'=>120,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>14,'name'=>'Rasmalai (4 pcs)','desc'=>'Soft rasmalai in chilled cardamom milk','price'=>200,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>60,'name'=>'Bhel Puri Plate','desc'=>'Crispy bhel puri with tangy chutneys','price'=>80,'veg'=>1],
    ],

    11 => [ // Sweet Corner Mithai Shop
        ['category_id'=>1,'subcategory_id'=>6,'name'=>'Gulab Jamun (6 pcs)','desc'=>'Deep-fried gulab jamuns in sugar syrup','price'=>120,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>2,'name'=>'Besan Burfi','desc'=>'Traditional besan burfi with cardamom','price'=>200,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>4,'name'=>'Kesar Peda (12 pcs)','desc'=>'Saffron-flavored milk pedas','price'=>240,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>15,'name'=>'Milk Cake (500g)','desc'=>'Alwar-style milk cake, caramelized perfection','price'=>280,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>11,'name'=>'Kalakand (250g)','desc'=>'Soft paneer kalakand with rose petals','price'=>180,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>10,'name'=>'Mysore Pak (200g)','desc'=>'Authentic Mysore pak with ghee','price'=>160,'veg'=>1],
    ],

    22 => [ // Royal Dine Pure Veg
        ['category_id'=>1,'subcategory_id'=>13,'name'=>'Dry Fruit Laddu Box','desc'=>'Healthy dry fruit laddus with dates and nuts','price'=>320,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>12,'name'=>'Modak (6 pcs)','desc'=>'Sweet modak with coconut and jaggery filling','price'=>150,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>63,'name'=>'Veg Samosa (4 pcs)','desc'=>'Crispy samosas stuffed with spiced potatoes','price'=>60,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>70,'name'=>'Khaman Dhokla','desc'=>'Soft and spongy khaman dhokla','price'=>50,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>64,'name'=>'Raj Kachori','desc'=>'Large crispy kachori with curd and chutneys','price'=>70,'veg'=>1],
    ],

    // ── Bakery (cat 3) ──
    15 => [ // Kiran Bakery & Snacks
        ['category_id'=>3,'subcategory_id'=>29,'name'=>'Chocolate Truffle Cake 1kg','desc'=>'Rich chocolate truffle cake for celebrations','price'=>650,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>33,'name'=>'Red Velvet Cupcakes (6 pcs)','desc'=>'Moist red velvet cupcakes with cream cheese frosting','price'=>300,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>37,'name'=>'Butter Cookies (200g)','desc'=>'Crispy butter cookies baked fresh daily','price'=>120,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>38,'name'=>'Multigrain Bread','desc'=>'Freshly baked multigrain bread loaf','price'=>65,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>35,'name'=>'Chocolate Brownie (4 pcs)','desc'=>'Fudgy chocolate brownies with walnuts','price'=>180,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>34,'name'=>'Vanilla Pastry (2 pcs)','desc'=>'Classic vanilla pastry with cream','price'=>80,'veg'=>1],
    ],

    17 => [ // Pizza Palace
        ['category_id'=>3,'subcategory_id'=>29,'name'=>'Margherita Pizza 8"','desc'=>'Classic margherita with mozzarella and basil','price'=>250,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>29,'name'=>'Farm Fresh Pizza 8"','desc'=>'Loaded with fresh vegetables and cheese','price'=>300,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>33,'name'=>'Garlic Breadsticks (6 pcs)','desc'=>'Crispy garlic breadsticks with herbs','price'=>150,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>35,'name'=>'Choco Lava Cake','desc'=>'Warm chocolate lava cake with molten center','price'=>180,'veg'=>1],
    ],

    // ── Beverages (cat 2) + Cold Drinks (cat 8) ──
    19 => [ // Juice Corner & Cold Drinks
        ['category_id'=>2,'subcategory_id'=>16,'name'=>'Fresh Orange Juice','desc'=>'Freshly squeezed orange juice','price'=>60,'veg'=>1],
        ['category_id'=>2,'subcategory_id'=>20,'name'=>'Mango Lassi','desc'=>'Creamy mango lassi with fresh mangoes','price'=>80,'veg'=>1],
        ['category_id'=>8,'subcategory_id'=>83,'name'=>'Watermelon Juice','desc'=>'Refreshing watermelon juice with mint','price'=>50,'veg'=>1],
        ['category_id'=>8,'subcategory_id'=>89,'name'=>'Cold Coffee','desc'=>'Iced cold coffee with whipped cream','price'=>90,'veg'=>1],
        ['category_id'=>8,'subcategory_id'=>90,'name'=>'Chocolate Milkshake','desc'=>'Rich chocolate milkshake with ice cream','price'=>100,'veg'=>1],
        ['category_id'=>5,'subcategory_id'=>51,'name'=>'Mango Ice Cream','desc'=>'Creamy mango ice cream cup','price'=>70,'veg'=>1],
    ],

    21 => [ // Chai Point Tea Stall
        ['category_id'=>2,'subcategory_id'=>16,'name'=>'Masala Chai','desc'=>'Authentic masala chai with ginger and cardamom','price'=>20,'veg'=>1],
        ['category_id'=>2,'subcategory_id'=>20,'name'=>'Sulaimani Chai','desc'=>'Spiced black tea with lemon','price'=>25,'veg'=>1],
        ['category_id'=>8,'subcategory_id'=>89,'name'=>'Iced Lemon Tea','desc'=>'Chilled lemon tea with fresh mint','price'=>40,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>37,'name'=>'Parle-G Biscuit Pack','desc'=>'Classic Parle-G biscuit pack','price'=>10,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>66,'name'=>'Butter Popcorn','desc'=>'Hot buttered popcorn','price'=>30,'veg'=>1],
    ],

    // ── Snacks & Namkeen (cat 6) ──
    14 => [ // Fresh Mart Grocery
        ['category_id'=>6,'subcategory_id'=>61,'name'=>'Aloo Bhujia (200g)','desc'=>'Haldiram style aloo bhujia','price'=>85,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>62,'name'=>'Navratan Mixture (250g)','desc'=>'Premium navratan mixture with dry fruits','price'=>120,'veg'=>1],
        ['category_id'=>7,'subcategory_id'=>72,'name'=>'Lays Classic Salted','desc'=>'Classic salted potato chips 52g','price'=>20,'veg'=>1],
        ['category_id'=>7,'subcategory_id'=>76,'name'=>'Kurkure Masala Munch','desc'=>'Crunchy kurkure masala munch 90g','price'=>20,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>67,'name'=>'Chakli (200g)','desc'=>'Crispy rice chakli, traditional recipe','price'=>90,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>68,'name'=>'Masala Peanuts (200g)','desc'=>'Roasted peanuts with spicy masala coating','price'=>70,'veg'=>1],
    ],

    23 => [ // Farm Fresh Vegetables
        ['category_id'=>6,'subcategory_id'=>69,'name'=>'Roasted Salted Cashews (100g)','desc'=>'Premium roasted cashews with sea salt','price'=>180,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>69,'name'=>'Roasted Almonds (100g)','desc'=>'Crunchy roasted almonds with light salt','price'=>160,'veg'=>1],
        ['category_id'=>8,'subcategory_id'=>84,'name'=>'Tender Coconut Water','desc'=>'Fresh tender coconut water','price'=>40,'veg'=>1],
        ['category_id'=>2,'subcategory_id'=>25,'name'=>'A2 Desi Ghee (500ml)','desc'=>'Pure A2 cow ghee, traditional bilona method','price'=>650,'veg'=>1],
        ['category_id'=>2,'subcategory_id'=>17,'name'=>'Fresh Paneer (250g)','desc'=>'Soft fresh paneer made from toned milk','price'=>90,'veg'=>1],
    ],

    20 => [ // Baba Food Corner
        ['category_id'=>6,'subcategory_id'=>63,'name'=>'Veg Samosa (2 pcs)','desc'=>'Hot crispy samosas with green chutney','price'=>30,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>71,'name'=>'Bhel Puri','desc'=>'Tangy bhel puri with sev and chutneys','price'=>40,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>37,'name'=>'Bread Pakora (2 pcs)','desc'=>'Deep-fried bread pakora with potato filling','price'=>40,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>65,'name'=>'Veg Puff','desc'=>'Flaky puff pastry with spiced veggie filling','price'=>25,'veg'=>1],
    ],

    // ── Non-Veg ──
    13 => [ // Spice Garden Non-Veg
        ['category_id'=>6,'subcategory_id'=>63,'name'=>'Chicken Samosa (4 pcs)','desc'=>'Crispy samosas with spiced chicken filling','price'=>100,'veg'=>0],
        ['category_id'=>9,'subcategory_id'=>92,'name'=>'Birthday Balloon Pack','desc'=>'Assorted colorful balloons (20 pcs)','price'=>80,'veg'=>1],
        ['category_id'=>9,'subcategory_id'=>94,'name'=>'Birthday Candles (12 pcs)','desc'=>'Colorful birthday candles','price'=>30,'veg'=>1],
        ['category_id'=>9,'subcategory_id'=>95,'name'=>'Happy Birthday Cake Topper','desc'=>'Gold glitter happy birthday topper','price'=>45,'veg'=>1],
    ],

    // ── Chinese ──
    16 => [ // Dragon Wok Chinese
        ['category_id'=>6,'subcategory_id'=>62,'name'=>'Hakka Noodles','desc'=>'Indo-Chinese hakka noodles with veggies','price'=>120,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>62,'name'=>'Schezwan Fried Rice','desc'=>'Spicy schezwan fried rice with vegetables','price'=>130,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>62,'name'=>'Veg Manchurian','desc'=>'Crispy veg manchurian in tangy sauce','price'=>140,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>62,'name'=>'Chilli Paneer Dry','desc'=>'Indo-Chinese chilli paneer with capsicum','price'=>150,'veg'=>1],
    ],

    // ── Tiffin / Home Food ──
    12 => [ // Tiffin Box Home Food
        ['category_id'=>1,'subcategory_id'=>5,'name'=>'Moong Dal Halwa','desc'=>'Rich moong dal halwa with ghee and dry fruits','price'=>100,'veg'=>1],
        ['category_id'=>1,'subcategory_id'=>8,'name'=>'Coconut Barfi','desc'=>'Soft coconut barfi with silver leaf','price'=>140,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>70,'name'=>'Idli Sambar (4 pcs)','desc'=>'Soft idli with hot sambar and chutney','price'=>60,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>70,'name'=>'Medu Vada (3 pcs)','desc'=>'Crispy medu vada with sambar','price'=>50,'veg'=>1],
        ['category_id'=>3,'subcategory_id'=>37,'name'=>'Rusk (200g)','desc'=>'Crispy tea-time rusk biscuits','price'=>35,'veg'=>1],
    ],

    // ── Amma Hotel South Indian ──
    18 => [
        ['category_id'=>6,'subcategory_id'=>70,'name'=>'Masala Dosa','desc'=>'Crispy masala dosa with potato filling','price'=>80,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>70,'name'=>'Rava Dosa','desc'=>'Thin crispy rava dosa with chutney','price'=>90,'veg'=>1],
        ['category_id'=>2,'subcategory_id'=>16,'name'=>'Filter Coffee','desc'=>'Authentic South Indian filter coffee','price'=>30,'veg'=>1],
        ['category_id'=>6,'subcategory_id'=>70,'name'=>'Onion Uttapam','desc'=>'Thick uttapam loaded with onions','price'=>70,'veg'=>1],
        ['category_id'=>5,'subcategory_id'=>52,'name'=>'Malai Kulfi','desc'=>'Traditional malai kulfi on stick','price'=>50,'veg'=>1],
    ],
];

$totalInserted = 0;

foreach ($shopItems as $shopId => $items) {
    foreach ($items as $item) {
        $offerPrice = $item['price'] > 100 ? round($item['price'] * 0.9) : null;
        $stmt = $pdo->prepare("
            INSERT INTO items (
                shop_id, category_id, subcategory_id, item_name, description,
                price, offer_price, status, is_veg, is_featured,
                min_quantity, weight_or_piece, preparation_time,
                gst_percent, cgst, sgst, igst, rating_avg, rating_count,
                created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, 'active', ?, ?,
                1, '1 unit', 15,
                5.00, 2.50, 2.50, 0.00, 0.00, 0,
                NOW(), NOW()
            )
        ");
        $isFeatured = ($item['price'] > 200) ? 1 : 0;
        $stmt->execute([
            $shopId,
            $item['category_id'],
            $item['subcategory_id'],
            $item['name'],
            $item['desc'],
            $item['price'],
            $offerPrice,
            $item['veg'],
            $isFeatured,
        ]);
        $itemId = $pdo->lastInsertId();
        $totalInserted++;

        // Add a default variant
        $variantPrice = $item['price'];
        $variantOffer = $offerPrice;
        $pdo->prepare("
            INSERT INTO item_variants (
                item_id, label, price, offer_price, is_default, status,
                gst_percent, cgst, sgst, igst, created_at, updated_at
            ) VALUES (?, 'Regular', ?, ?, 1, 'active', 5.00, 2.50, 2.50, 0.00, NOW(), NOW())
        ")->execute([$itemId, $variantPrice, $variantOffer]);

        echo "  ✓ #{$itemId} {$item['name']} — Shop #{$shopId}\n";
    }
}

$newCount = $pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
$variantCount = $pdo->query('SELECT COUNT(*) FROM item_variants')->fetchColumn();
echo "\nDone! Inserted {$totalInserted} items. Total items: {$newCount}, Variants: {$variantCount}\n";
