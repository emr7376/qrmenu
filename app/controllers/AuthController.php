<?php

class AuthController
{
    public static function showLogin(): void
    {
        $db = Database::get();
        $plans = $db->query('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC')->fetch_all(MYSQLI_ASSOC);
        view('auth/login', [
            'title' => 'Ücretsiz Üye Ol - QR Menü ile Dijital Restoran Menüsü Oluşturun',
            'metaDescription' => 'QRMenü\'ye ücretsiz üye olun, restoranınız için QR kodlu dijital menünüzü 5 dakikada oluşturun. 7 gün ücretsiz deneme, kredi kartı gerekmez.',
            'plans' => $plans,
            'error' => flash('error'),
            'signupError' => flash('signup_error'),
            'old' => flash('signup_old') ?? [],
            'defaultTab' => ($_GET['tab'] ?? '') === 'signup' ? 'signup' : 'login',
            'selectedPlanId' => (int) ($_GET['plan'] ?? 0),
        ]);
    }

    public static function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $restaurant = Auth::verifyPassword($email, $password);
        if (!$restaurant) {
            flash('error', 'E-posta veya şifre hatalı.');
            redirect('/login');
        }

        self::issueAndSendCode($restaurant);
        $_SESSION['login_otp_pending_id'] = (int) $restaurant['id'];
        redirect('/login/verify');
    }

    public static function showVerify(): void
    {
        if (empty($_SESSION['login_otp_pending_id'])) {
            redirect('/login');
        }
        view('auth/verify_code', [
            'title' => 'Giriş Kodu - QR Menü',
            'error' => flash('error'),
            'info' => flash('info'),
        ]);
    }

    private const MAX_OTP_ATTEMPTS = 5;

    public static function verifyCode(): void
    {
        $restaurantId = (int) ($_SESSION['login_otp_pending_id'] ?? 0);
        if ($restaurantId === 0) {
            redirect('/login');
        }

        $code = trim($_POST['code'] ?? '');
        $db = Database::get();
        $stmt = $db->prepare('SELECT login_otp_code, login_otp_expires_at, login_otp_attempts FROM restaurants WHERE id = ?');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row && (int) $row['login_otp_attempts'] >= self::MAX_OTP_ATTEMPTS) {
            flash('error', 'Çok fazla hatalı deneme yaptınız. Lütfen yeni bir kod isteyin.');
            redirect('/login/verify');
        }

        $isValid = $row && $row['login_otp_code'] !== null
            && hash_equals((string) $row['login_otp_code'], $code)
            && $row['login_otp_expires_at'] !== null
            && new DateTime($row['login_otp_expires_at']) >= new DateTime();

        if (!$isValid) {
            $fail = $db->prepare('UPDATE restaurants SET login_otp_attempts = login_otp_attempts + 1 WHERE id = ?');
            $fail->bind_param('i', $restaurantId);
            $fail->execute();
            flash('error', 'Kod hatalı veya süresi dolmuş.');
            redirect('/login/verify');
        }

        $clear = $db->prepare('UPDATE restaurants SET login_otp_code = NULL, login_otp_expires_at = NULL, login_otp_attempts = 0 WHERE id = ?');
        $clear->bind_param('i', $restaurantId);
        $clear->execute();

        unset($_SESSION['login_otp_pending_id']);
        Auth::login($restaurantId);
        redirect('/admin');
    }

    private const RESEND_COOLDOWN_SECONDS = 30;

    public static function resendCode(): void
    {
        $restaurantId = (int) ($_SESSION['login_otp_pending_id'] ?? 0);
        if ($restaurantId === 0) {
            redirect('/login');
        }
        $lastSentAt = $_SESSION['login_otp_resent_at'] ?? 0;
        if (time() - $lastSentAt < self::RESEND_COOLDOWN_SECONDS) {
            flash('info', 'Yeni kod istemeden önce birkaç saniye bekleyin.');
            redirect('/login/verify');
        }
        $_SESSION['login_otp_resent_at'] = time();
        $db = Database::get();
        $stmt = $db->prepare('SELECT id, name, email FROM restaurants WHERE id = ?');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        $restaurant = $stmt->get_result()->fetch_assoc();
        if (!$restaurant) {
            redirect('/login');
        }
        self::issueAndSendCode($restaurant);
        flash('info', 'Yeni kod gönderildi.');
        redirect('/login/verify');
    }

    private static function issueAndSendCode(array $restaurant): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = (new DateTime())->modify('+10 minutes')->format('Y-m-d H:i:s');

        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET login_otp_code = ?, login_otp_expires_at = ?, login_otp_attempts = 0 WHERE id = ?');
        $stmt->bind_param('ssi', $code, $expiresAt, $restaurant['id']);
        $stmt->execute();

        Mailer::sendLoginCode($restaurant['email'], $restaurant['name'], $code);
    }

    public static function logout(): void
    {
        Auth::logout();
        redirect('/');
    }

    public static function signup(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $planId = (int) ($_POST['plan_id'] ?? 0);

        if ($name === '' || $email === '' || $password === '' || $planId === 0) {
            flash('signup_error', 'Lütfen tüm alanları doldurun.');
            flash('signup_old', ['name' => $name, 'email' => $email]);
            redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('signup_error', 'Geçerli bir e-posta adresi girin.');
            flash('signup_old', ['name' => $name, 'email' => $email]);
            redirect('/login');
        }

        if (strlen($password) < 6) {
            flash('signup_error', 'Şifre en az 6 karakter olmalı.');
            flash('signup_old', ['name' => $name, 'email' => $email]);
            redirect('/login');
        }

        $db = Database::get();
        $stmt = $db->prepare('SELECT id FROM restaurants WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            flash('signup_error', 'Bu e-posta ile zaten bir restoran kaydı var.');
            flash('signup_old', ['name' => $name, 'email' => $email]);
            redirect('/login');
        }

        $planStmt = $db->prepare('SELECT id FROM plans WHERE id = ? AND is_active = 1');
        $planStmt->bind_param('i', $planId);
        $planStmt->execute();
        if (!$planStmt->get_result()->fetch_assoc()) {
            flash('signup_error', 'Geçersiz plan seçimi.');
            flash('signup_old', ['name' => $name, 'email' => $email]);
            redirect('/login');
        }

        $baseSlug = slugify($name);
        $slug = $baseSlug !== '' ? $baseSlug : 'restoran-' . substr(md5($email), 0, 6);
        $suffix = 1;
        $checkStmt = $db->prepare('SELECT id FROM restaurants WHERE slug = ?');
        while (true) {
            $checkStmt->bind_param('s', $slug);
            $checkStmt->execute();
            if (!$checkStmt->get_result()->fetch_assoc()) {
                break;
            }
            $suffix++;
            $slug = $baseSlug . '-' . $suffix;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $trialEndsAt = (new DateTime())->modify('+' . OM_TRIAL_DAYS . ' days')->format('Y-m-d H:i:s');

        $insert = $db->prepare(
            'INSERT INTO restaurants (name, slug, email, password_hash, plan_id, subscription_status, trial_ends_at, is_open)
             VALUES (?, ?, ?, ?, ?, "trial", ?, 1)'
        );
        $insert->bind_param('ssssis', $name, $slug, $email, $passwordHash, $planId, $trialEndsAt);
        $insert->execute();

        Auth::attempt($email, $password);
        redirect('/admin');
    }
}
