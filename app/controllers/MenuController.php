<?php

class MenuController
{
    private static function loadRestaurant(string $slug): ?array
    {
        $db = Database::get();
        $stmt = $db->prepare(
            'SELECT r.*, p.can_use_categories, p.can_upload_images, p.can_customize_theme, p.can_feature_products
             FROM restaurants r JOIN plans p ON p.id = r.plan_id WHERE r.slug = ?'
        );
        $stmt->bind_param('s', $slug);
        $stmt->execute();
        $restaurant = $stmt->get_result()->fetch_assoc();
        if (!$restaurant) {
            return null;
        }
        $restaurant = syncSubscriptionStatus($restaurant);
        // Plan düşürüldüğünde (ör. premium'dan basic'e) o plana özel veriler (galeri,
        // hakkımızda metni/logosu, kategori grupları, öne çıkan rozeti) silinmiyor —
        // sadece mevcut plan bunları desteklemediği sürece public sitede gösterilmiyor.
        // Plan tekrar yükseltilirse hepsi aynen geri görünür.
        if (!$restaurant['can_upload_images']) {
            $restaurant['about_text'] = null;
            $restaurant['logo_path'] = null;
        }
        return $restaurant;
    }

    private static function isVisible(array $restaurant): bool
    {
        return $restaurant['is_open'] && !in_array($restaurant['subscription_status'], ['expired', 'canceled'], true);
    }

    private static function galleryFor(array $restaurant): array
    {
        if (!$restaurant['can_upload_images']) {
            return [];
        }
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM restaurant_gallery WHERE restaurant_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->bind_param('i', $restaurant['id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private static function itemsFor(array $restaurant): array
    {
        $db = Database::get();
        $stmt = $db->prepare(
            'SELECT mi.*, mc.name AS category_name, mc.sort_order AS category_sort
             FROM menu_items mi LEFT JOIN menu_categories mc ON mc.id = mi.category_id AND mc.restaurant_id = mi.restaurant_id
             WHERE mi.restaurant_id = ? AND mi.is_available = 1
             ORDER BY COALESCE(mc.sort_order, 0) ASC, mc.id ASC, mi.sort_order ASC, mi.id ASC'
        );
        $stmt->bind_param('i', $restaurant['id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private static function groupItems(array $items, bool $categoriesEnabled, bool $featuredEnabled): array
    {
        $grouped = [];
        $featured = [];
        foreach ($items as $item) {
            $key = ($categoriesEnabled && $item['category_name']) ? $item['category_name'] : 'Menü';
            $grouped[$key][] = $item;
            if ($featuredEnabled && !empty($item['is_featured'])) {
                $featured[] = $item;
            }
        }
        return [$grouped, $featured];
    }

    private static function handleMissingOrClosed(?array $restaurant): bool
    {
        if (!$restaurant) {
            http_response_code(404);
            view('menu/not_found', ['title' => 'Menü Bulunamadı']);
            return true;
        }
        if (!self::isVisible($restaurant)) {
            view('menu/closed', ['title' => e($restaurant['name']) . ' - Menü Kapalı', 'restaurant' => $restaurant]);
            return true;
        }
        return false;
    }

    public static function home(string $slug): void
    {
        $restaurant = self::loadRestaurant($slug);
        if (self::handleMissingOrClosed($restaurant)) {
            return;
        }

        $db = Database::get();
        $visitStmt = $db->prepare('INSERT INTO menu_visits (restaurant_id) VALUES (?)');
        $visitStmt->bind_param('i', $restaurant['id']);
        $visitStmt->execute();

        [, $featured] = self::groupItems(self::itemsFor($restaurant), (bool) $restaurant['can_use_categories'], (bool) $restaurant['can_feature_products']);

        view('menu/home', [
            'title' => $restaurant['name'] . ' - Anasayfa',
            'activePage' => 'home',
            'restaurant' => $restaurant,
            'gallery' => self::galleryFor($restaurant),
            'featured' => array_slice($featured, 0, 3),
        ]);
    }

    public static function about(string $slug): void
    {
        $restaurant = self::loadRestaurant($slug);
        if (self::handleMissingOrClosed($restaurant)) {
            return;
        }

        view('menu/about', [
            'title' => 'Hakkımızda - ' . $restaurant['name'],
            'activePage' => 'about',
            'restaurant' => $restaurant,
            'gallery' => self::galleryFor($restaurant),
        ]);
    }

    public static function menuPage(string $slug): void
    {
        $restaurant = self::loadRestaurant($slug);
        if (self::handleMissingOrClosed($restaurant)) {
            return;
        }

        [$grouped, $featured] = self::groupItems(self::itemsFor($restaurant), (bool) $restaurant['can_use_categories'], (bool) $restaurant['can_feature_products']);

        view('menu/menu', [
            'title' => 'Menü - ' . $restaurant['name'],
            'activePage' => 'menu',
            'restaurant' => $restaurant,
            'grouped' => $grouped,
            'featured' => $featured,
        ]);
    }

    public static function location(string $slug): void
    {
        $restaurant = self::loadRestaurant($slug);
        if (self::handleMissingOrClosed($restaurant)) {
            return;
        }

        view('menu/location', [
            'title' => t('contact_title') . ' - ' . $restaurant['name'],
            'activePage' => 'location',
            'restaurant' => $restaurant,
        ]);
    }
}
