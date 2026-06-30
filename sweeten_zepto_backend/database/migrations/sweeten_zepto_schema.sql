-- ============================================================
-- SWEETEN APP — ZEPTO/BLINKIT STYLE DB ADDITIONS
-- Run these after your existing migrations
-- ============================================================


ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS coupon_id BIGINT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS coupon_code VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS wallet_used DECIMAL(10,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS payment_method ENUM('cod','online','wallet') DEFAULT 'cod',
    ADD COLUMN IF NOT EXISTS payment_status ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
    ADD COLUMN IF NOT EXISTS payment_reference VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS special_instructions TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS rated_at TIMESTAMP DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS shop_rating TINYINT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS delivery_rating TINYINT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS expected_delivery_at TIMESTAMP DEFAULT NULL;

ALTER TABLE cart_items
    ADD COLUMN IF NOT EXISTS variant_id BIGINT UNSIGNED DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS item_name VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS variant_label VARCHAR(100) DEFAULT NULL;

ALTER TABLE app_users
    ADD COLUMN IF NOT EXISTS referral_code VARCHAR(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS referred_by VARCHAR(20) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS dob DATE DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS gender ENUM('male','female','other') DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS picture VARCHAR(500) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS wallet_balance DECIMAL(12,2) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS is_blocked TINYINT(1) DEFAULT 0,
    ADD COLUMN IF NOT EXISTS fcm_token VARCHAR(500) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS last_active_at TIMESTAMP DEFAULT NULL;

CREATE TABLE IF NOT EXISTS coupons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    discount_type ENUM('percent','flat') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL DEFAULT 0,
    min_order_amount DECIMAL(10,2) DEFAULT 0,
    max_discount_amount DECIMAL(10,2) DEFAULT NULL,
    usage_limit INT UNSIGNED DEFAULT NULL,
    usage_per_user INT UNSIGNED DEFAULT 1,
    used_count INT UNSIGNED DEFAULT 0,
    applicable_to ENUM('all','category','shop','item') DEFAULT 'all',
    applicable_ids JSON DEFAULT NULL,
    valid_from DATETIME NOT NULL,
    valid_until DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS coupon_usages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED DEFAULT NULL,
    discount_given DECIMAL(10,2) NOT NULL DEFAULT 0,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS deals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    banner_image VARCHAR(500) DEFAULT NULL,
    deal_type ENUM('flash_sale','bundle','buy_x_get_y','free_delivery') DEFAULT 'flash_sale',
    discount_type ENUM('percent','flat') DEFAULT 'percent',
    discount_value DECIMAL(10,2) DEFAULT 0,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS deal_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    deal_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    deal_price DECIMAL(10,2) DEFAULT NULL,
    deal_discount_percent DECIMAL(5,2) DEFAULT NULL,
    stock_limit INT UNSIGNED DEFAULT NULL,
    sold_count INT UNSIGNED DEFAULT 0,
    FOREIGN KEY (deal_id) REFERENCES deals(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS wallet_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('credit','debit') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    reference_type VARCHAR(50) DEFAULT NULL,
    reference_id BIGINT UNSIGNED DEFAULT NULL,
    balance_after DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS referral_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL UNIQUE,
    reward_referrer DECIMAL(10,2) DEFAULT 50,
    reward_referee DECIMAL(10,2) DEFAULT 30,
    usage_count INT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    user_type ENUM('app_user','delivery_boy','shop_owner','all') DEFAULT 'app_user',
    title VARCHAR(255) NOT NULL,
    body TEXT,
    type VARCHAR(50) DEFAULT 'general',
    reference_type VARCHAR(50) DEFAULT NULL,
    reference_id BIGINT UNSIGNED DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shop_reviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT DEFAULT NULL,
    is_approved TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_order_review (order_id, user_id)
);

CREATE TABLE IF NOT EXISTS delivery_earnings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    delivery_boy_id BIGINT UNSIGNED NOT NULL,
    order_id BIGINT UNSIGNED NOT NULL,
    base_earning DECIMAL(10,2) DEFAULT 0,
    bonus DECIMAL(10,2) DEFAULT 0,
    deduction DECIMAL(10,2) DEFAULT 0,
    net_earning DECIMAL(10,2) DEFAULT 0,
    is_paid TINYINT(1) DEFAULT 0,
    paid_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS app_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT,
    label VARCHAR(150),
    group_name VARCHAR(100) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO app_settings (`key`, value, label, group_name) VALUES
('free_delivery_above', '199', 'Free Delivery Above (Rs)', 'delivery'),
('delivery_base_charge', '30', 'Base Delivery Charge (Rs)', 'delivery'),
('min_order_amount', '99', 'Minimum Order Amount (Rs)', 'order'),
('referral_enabled', '1', 'Referral Program Enabled', 'referral'),
('wallet_enabled', '1', 'Wallet Enabled', 'wallet'),
('cod_enabled', '1', 'Cash on Delivery Enabled', 'payment'),
('max_delivery_radius_km', '10', 'Max Delivery Radius (km)', 'delivery'),
('delivery_earn_per_order', '30', 'Delivery Boy Earn Per Order (Rs)', 'delivery');

CREATE TABLE IF NOT EXISTS wishlists (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, item_id)
);

CREATE TABLE IF NOT EXISTS search_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED DEFAULT NULL,
    query VARCHAR(255) NOT NULL,
    result_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS shop_schedules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT UNSIGNED NOT NULL,
    day_of_week TINYINT UNSIGNED NOT NULL COMMENT '0=Sun 1=Mon 6=Sat',
    open_time TIME NOT NULL,
    close_time TIME NOT NULL,
    is_closed TINYINT(1) DEFAULT 0,
    UNIQUE KEY unique_schedule (shop_id, day_of_week)
);
