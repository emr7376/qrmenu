<?php

class OnboardingController
{
    private static function guard(): array
    {
        Auth::requireLogin();
        $restaurant = Auth::restaurant();
        if (!$restaurant) {
            Auth::logout();
            redirect('/login');
        }
        return $restaurant;
    }

    private static function productCount(int $restaurantId): int
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT COUNT(*) AS c FROM menu_items WHERE restaurant_id = ?');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['c'];
    }

    public static function show(): void
    {
        $restaurant = self::guard();
        if ($restaurant['onboarding_completed']) {
            redirect('/admin');
        }

        $hasProduct = self::productCount((int) $restaurant['id']) > 0;

        view('admin/onboarding', [
            'title' => 'Kurulum - QR Menü',
            'restaurant' => $restaurant,
            'step' => $hasProduct ? 'done' : 'product',
            'menuUrl' => menuUrl($restaurant['slug']),
            'error' => flash('error'),
        ]);
    }

    public static function addFirstProduct(): void
    {
        $restaurant = self::guard();
        $name = trim($_POST['name'] ?? '');
        $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');

        if ($name === '') {
            flash('error', 'Ürün adı zorunlu.');
            redirect('/admin/onboarding');
        }

        $db = Database::get();
        $stmt = $db->prepare(
            'INSERT INTO menu_items (restaurant_id, name, price, is_available) VALUES (?, ?, ?, 1)'
        );
        $stmt->bind_param('isd', $restaurant['id'], $name, $price);
        $stmt->execute();

        redirect('/admin/onboarding');
    }

    public static function finish(): void
    {
        $restaurant = self::guard();
        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET onboarding_completed = 1 WHERE id = ?');
        $stmt->bind_param('i', $restaurant['id']);
        $stmt->execute();
        redirect('/admin');
    }
}
