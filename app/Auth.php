<?php

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT id, password_hash FROM restaurants WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result && password_verify($password, $result['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['restaurant_id'] = (int) $result['id'];
            return true;
        }
        return false;
    }

    // Şifreyi doğrular ama oturumu AÇMAZ — giriş kodu (2FA) doğrulanana kadar tam giriş beklemede kalır.
    public static function verifyPassword(string $email, string $password): ?array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT id, name, email, password_hash FROM restaurants WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if ($result && password_verify($password, $result['password_hash'])) {
            return $result;
        }
        return null;
    }

    public static function login(int $restaurantId): void
    {
        session_regenerate_id(true);
        $_SESSION['restaurant_id'] = $restaurantId;
    }

    public static function logout(): void
    {
        unset($_SESSION['restaurant_id']);
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['restaurant_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['restaurant_id'] ?? null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }

    public static function restaurant(): ?array
    {
        if (!self::check()) {
            return null;
        }
        $db = Database::get();
        $stmt = $db->prepare(
            'SELECT r.*, p.slug AS plan_slug, p.name AS plan_name, p.price_monthly, p.max_products, p.can_upload_images, p.can_use_categories, p.can_customize_theme,
                    p.can_feature_products, p.can_view_analytics
             FROM restaurants r JOIN plans p ON p.id = r.plan_id
             WHERE r.id = ?'
        );
        $id = self::id();
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $restaurant = $stmt->get_result()->fetch_assoc();
        return $restaurant ?: null;
    }
}
