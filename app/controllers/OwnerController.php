<?php

class OwnerController
{
    public static function showLogin(): void
    {
        if (OwnerAuth::check()) {
            redirect('/superadmin');
        }
        view('owner/login', ['title' => 'Yönetici Girişi', 'error' => flash('owner_error')]);
    }

    public static function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (OwnerAuth::attempt($email, $password)) {
            redirect('/superadmin');
        }
        flash('owner_error', 'E-posta veya şifre hatalı.');
        redirect('/superadmin/login');
    }

    public static function logout(): void
    {
        OwnerAuth::logout();
        redirect('/superadmin/login');
    }

    public static function dashboard(): void
    {
        OwnerAuth::requireLogin();
        $db = Database::get();
        $restaurants = $db->query(
            'SELECT r.*, p.name AS plan_name, p.id AS plan_id, p.price_monthly,
                    (SELECT COUNT(*) FROM menu_items mi WHERE mi.restaurant_id = r.id) AS product_count
             FROM restaurants r JOIN plans p ON p.id = r.plan_id
             ORDER BY r.created_at DESC'
        )->fetch_all(MYSQLI_ASSOC);
        $plans = $db->query('SELECT * FROM plans ORDER BY sort_order ASC')->fetch_all(MYSQLI_ASSOC);

        $counts = ['total' => count($restaurants), 'trial' => 0, 'active' => 0, 'expired' => 0, 'canceled' => 0];
        $totalRevenue = 0;
        $revenueByPlan = [];
        foreach ($restaurants as $r) {
            $counts[$r['subscription_status']] = ($counts[$r['subscription_status']] ?? 0) + 1;
            if ($r['subscription_status'] === 'active') {
                $totalRevenue += (float) $r['price_monthly'];
                $planName = $r['plan_name'];
                if (!isset($revenueByPlan[$planName])) {
                    $revenueByPlan[$planName] = ['count' => 0, 'revenue' => 0];
                }
                $revenueByPlan[$planName]['count']++;
                $revenueByPlan[$planName]['revenue'] += (float) $r['price_monthly'];
            }
        }

        view('owner/dashboard', [
            'title' => 'Yönetici Paneli',
            'restaurants' => $restaurants,
            'plans' => $plans,
            'totalRevenue' => $totalRevenue,
            'revenueByPlan' => $revenueByPlan,
            'counts' => $counts,
            'success' => flash('owner_success'),
        ]);
    }

    public static function newRestaurantForm(): void
    {
        OwnerAuth::requireLogin();
        $db = Database::get();
        $plans = $db->query('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC')->fetch_all(MYSQLI_ASSOC);
        view('owner/restaurant_form', [
            'title' => 'Yeni Restoran Ekle',
            'plans' => $plans,
            'error' => flash('owner_error'),
        ]);
    }

    public static function createRestaurant(): void
    {
        OwnerAuth::requireLogin();
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $password = trim($_POST['password'] ?? '');

        if ($name === '' || $email === '' || $planId === 0) {
            flash('owner_error', 'Lütfen tüm alanları doldurun.');
            redirect('/superadmin/restaurants/new');
        }

        $db = Database::get();
        $stmt = $db->prepare('SELECT id FROM restaurants WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            flash('owner_error', 'Bu e-posta ile zaten bir restoran kaydı var.');
            redirect('/superadmin/restaurants/new');
        }

        if ($password === '') {
            $password = bin2hex(random_bytes(4));
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

        flash('owner_success', 'Restoran oluşturuldu. "' . $email . '" için giriş şifresi: ' . $password . ' (bu şifreyi restorana iletin, tekrar gösterilmeyecek)');
        redirect('/superadmin');
    }

    public static function updateRestaurant(int $id): void
    {
        OwnerAuth::requireLogin();
        $status = $_POST['subscription_status'] ?? 'trial';
        $planId = (int) ($_POST['plan_id'] ?? 0);
        $isOpen = isset($_POST['is_open']) ? 1 : 0;

        $allowedStatuses = ['trial', 'active', 'expired', 'canceled'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'trial';
        }

        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET subscription_status = ?, plan_id = ?, is_open = ? WHERE id = ?');
        $stmt->bind_param('siii', $status, $planId, $isOpen, $id);
        $stmt->execute();

        flash('owner_success', 'Restoran güncellendi.');
        redirect('/superadmin');
    }

    private const BROWSABLE_TABLES = [
        'restaurants', 'plans', 'menu_categories', 'menu_items', 'menu_visits', 'restaurant_gallery', 'admins',
    ];

    private const MASKED_COLUMNS = ['password_hash'];

    public static function database(): void
    {
        OwnerAuth::requireLogin();
        $db = Database::get();

        $table = $_GET['table'] ?? self::BROWSABLE_TABLES[0];
        if (!in_array($table, self::BROWSABLE_TABLES, true)) {
            $table = self::BROWSABLE_TABLES[0];
        }

        $countResult = $db->query("SELECT COUNT(*) AS c FROM `$table`")->fetch_assoc();
        $totalRows = (int) $countResult['c'];

        $result = $db->query("SELECT * FROM `$table` ORDER BY id DESC LIMIT 200");
        $rows = $result->fetch_all(MYSQLI_ASSOC);
        $columns = $rows ? array_keys($rows[0]) : [];

        foreach ($rows as &$row) {
            foreach (self::MASKED_COLUMNS as $masked) {
                if (array_key_exists($masked, $row)) {
                    $row[$masked] = '••••••••';
                }
            }
        }
        unset($row);

        $lookups = [
            'restaurant_id' => self::lookupMap($db, 'restaurants', 'name'),
            'plan_id' => self::lookupMap($db, 'plans', 'name'),
            'category_id' => self::lookupMap($db, 'menu_categories', 'name'),
        ];

        $itemsByCategory = [];
        if ($table === 'menu_categories') {
            $itemsResult = $db->query(
                'SELECT id, category_id, name, price, is_available FROM menu_items WHERE category_id IS NOT NULL ORDER BY sort_order ASC'
            );
            while ($item = $itemsResult->fetch_assoc()) {
                $itemsByCategory[$item['category_id']][] = $item;
            }
        }

        $groupedByRestaurant = null;
        if (in_array('restaurant_id', $columns, true)) {
            $groupedByRestaurant = [];
            foreach ($rows as $row) {
                $groupedByRestaurant[$row['restaurant_id']][] = $row;
            }
        }

        view('owner/database', [
            'title' => 'Veritabanı - Yönetici Paneli',
            'tables' => self::BROWSABLE_TABLES,
            'currentTable' => $table,
            'columns' => $columns,
            'rows' => $rows,
            'groupedByRestaurant' => $groupedByRestaurant,
            'totalRows' => $totalRows,
            'lookups' => $lookups,
            'itemsByCategory' => $itemsByCategory,
        ]);
    }

    private static function lookupMap(mysqli $db, string $table, string $labelColumn): array
    {
        $map = [];
        $result = $db->query("SELECT id, `$labelColumn` FROM `$table`");
        while ($row = $result->fetch_assoc()) {
            $map[$row['id']] = $row[$labelColumn];
        }
        return $map;
    }

    public static function plans(): void
    {
        OwnerAuth::requireLogin();
        $db = Database::get();
        $plans = $db->query('SELECT * FROM plans ORDER BY sort_order ASC')->fetch_all(MYSQLI_ASSOC);
        view('owner/plans', [
            'title' => 'Planları Yönet',
            'plans' => $plans,
            'success' => flash('owner_success'),
        ]);
    }

    public static function updatePlan(int $id): void
    {
        OwnerAuth::requireLogin();
        $name = trim($_POST['name'] ?? '');
        $price = (float) str_replace(',', '.', $_POST['price_monthly'] ?? '0');
        $maxProducts = trim($_POST['max_products'] ?? '');
        $maxProducts = $maxProducts === '' ? null : (int) $maxProducts;
        $flags = ['can_upload_images', 'can_use_categories', 'can_customize_theme', 'can_feature_products', 'can_view_analytics'];

        $db = Database::get();
        $sets = ['name = ?', 'price_monthly = ?', 'max_products = ?'];
        $types = 'sdi';
        $values = [$name, $price, $maxProducts];
        foreach ($flags as $flag) {
            $sets[] = "$flag = ?";
            $types .= 'i';
            $values[] = isset($_POST[$flag]) ? 1 : 0;
        }
        $sql = 'UPDATE plans SET ' . implode(', ', $sets) . ' WHERE id = ?';
        $types .= 'i';
        $values[] = $id;

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$values);
        $stmt->execute();

        flash('owner_success', 'Plan güncellendi.');
        redirect('/superadmin/plans');
    }
}
