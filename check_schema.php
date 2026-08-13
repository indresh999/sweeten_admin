<?php
$pdo = new PDO('mysql:host=72.62.242.223;dbname=backup;charset=utf8mb4','backup','SD3SsDWNKNtDScmn');

// Check categories
$stmt = $pdo->query('SELECT id, category_name FROM item_categories WHERE status=1 ORDER BY id');
echo "=== Categories ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) echo "#{$r['id']} {$r['category_name']}\n";

// Check subcategories
$stmt = $pdo->query('SELECT id, category_id, name FROM item_subcategories WHERE status=1 ORDER BY category_id, sort_order');
echo "\n=== Subcategories ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) echo "#{$r['id']} cat={$r['category_id']} {$r['name']}\n";

// Check items table structure
$stmt = $pdo->query('DESCRIBE items');
echo "\n=== Items columns ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) echo $r['Field'] . ' (' . $r['Type'] . ")\n";

// Check item_variants structure
$stmt = $pdo->query('DESCRIBE item_variants');
echo "\n=== Item Variants columns ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) echo $r['Field'] . ' (' . $r['Type'] . ")\n";

// Check existing items count
$count = $pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
echo "\nExisting items: $count\n";

// Check shops
$stmt = $pdo->query("SELECT shop_id, restaurant_name FROM app_owner_shops WHERE status='active' ORDER BY shop_id");
echo "\n=== Active Shops ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) echo "#{$r['shop_id']} {$r['restaurant_name']}\n";
