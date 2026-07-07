<?php

class OwnerAuth
{
    public static function attempt(string $email, string $password): bool
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT id, password_hash FROM admins WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_id'] = (int) $admin['id'];
            return true;
        }
        return false;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id']);
    }

    public static function check(): bool
    {
        return isset($_SESSION['admin_id']);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/superadmin/login');
        }
    }
}
