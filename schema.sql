-- QR Menü SaaS - güncel veritabanı şeması (konsolide edilmiş, 2026-07-06)
-- DB: onlinemenu (WordPress'in `local` veritabanından bağımsız)
-- Bu dosya baştan çalıştırılabilir tek şema dosyasıdır — eski migration_2..6.sql dosyaları bunun içine konsolide edildi.

CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    price_monthly DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_products INT NULL, -- NULL = sınırsız (şu an tüm planlarda sınırsız)
    can_upload_images TINYINT(1) NOT NULL DEFAULT 1,
    can_use_categories TINYINT(1) NOT NULL DEFAULT 0,
    can_customize_theme TINYINT(1) NOT NULL DEFAULT 0,
    can_feature_products TINYINT(1) NOT NULL DEFAULT 0,
    can_view_analytics TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    plan_id INT NOT NULL,
    subscription_status ENUM('trial','active','expired','canceled') NOT NULL DEFAULT 'trial',
    trial_ends_at DATETIME NOT NULL,
    is_open TINYINT(1) NOT NULL DEFAULT 1,
    qr_color VARCHAR(7) NOT NULL DEFAULT '#24201d',
    qr_logo_path VARCHAR(255) NULL,
    theme_color VARCHAR(7) NULL, -- can_customize_theme (Premium) planındaki restoranın kendi public menüsündeki aksan rengi
    login_otp_code VARCHAR(6) NULL, -- giriş e-posta doğrulama kodu (2FA), her girişte yeniden üretilir
    login_otp_expires_at DATETIME NULL,
    logo_path VARCHAR(255) NULL,
    contact_phone VARCHAR(50) NULL,
    contact_address VARCHAR(255) NULL,
    contact_instagram VARCHAR(150) NULL,
    contact_whatsapp VARCHAR(50) NULL,
    contact_facebook VARCHAR(190) NULL,
    contact_x VARCHAR(190) NULL,
    latitude DECIMAL(10,7) NULL,
    longitude DECIMAL(10,7) NULL,
    about_text TEXT NULL,
    billing_identity_number VARCHAR(11) NULL, -- TC kimlik/vergi no, iyzico buyer.identityNumber için zorunlu
    billing_city VARCHAR(100) NULL,
    iyzico_card_user_key VARCHAR(64) NULL,
    iyzico_card_token VARCHAR(64) NULL,
    next_billing_at DATETIME NULL, -- bir sonraki otomatik tahsilat tarihi (kart kaydedilince set edilir)
    payment_retry_count INT NOT NULL DEFAULT 0, -- üst üste başarısız otomatik tahsilat denemesi sayacı
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES plans(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('success','failure') NOT NULL,
    iyzico_payment_id VARCHAR(64) NULL,
    error_message VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS restaurant_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    category_id INT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NULL,
    is_available TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Restoran planları (fiyatlar sadece gösterim amaçlı, gerçek ödeme entegrasyonu yok)
-- Premium'un Pro'dan net ayrışması için analitik SADECE Premium'da (2026-07-06 kararı)
INSERT INTO plans (slug, name, price_monthly, max_products, can_upload_images, can_use_categories, can_customize_theme, can_feature_products, can_view_analytics, sort_order)
VALUES
    ('basic', 'Başlangıç', 99.00, NULL, 0, 0, 0, 0, 0, 1),
    ('pro', 'Profesyonel', 199.00, NULL, 1, 1, 0, 1, 0, 2),
    ('premium', 'Premium', 349.00, NULL, 1, 1, 1, 1, 1, 3)
ON DUPLICATE KEY UPDATE
    name = VALUES(name), price_monthly = VALUES(price_monthly), max_products = VALUES(max_products),
    can_upload_images = VALUES(can_upload_images), can_use_categories = VALUES(can_use_categories),
    can_customize_theme = VALUES(can_customize_theme), can_feature_products = VALUES(can_feature_products),
    can_view_analytics = VALUES(can_view_analytics);
