-- Restoran kimliği: hakkımızda yazısı + fotoğraf galerisi (can_upload_images ile aynı plan kuralına tabi)

ALTER TABLE restaurants
    ADD COLUMN about_text TEXT NULL AFTER contact_whatsapp;

CREATE TABLE IF NOT EXISTS restaurant_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
