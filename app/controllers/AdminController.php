<?php

class AdminController
{
    private static function guard(): array
    {
        Auth::requireLogin();
        $restaurant = Auth::restaurant();
        if (!$restaurant) {
            Auth::logout();
            redirect('/login');
        }
        $restaurant = syncSubscriptionStatus($restaurant);
        return $restaurant;
    }

    private static function resolveOwnedCategoryId(?int $categoryId, int $restaurantId): ?int
    {
        if (!$categoryId) {
            return null;
        }
        $db = Database::get();
        $stmt = $db->prepare('SELECT id FROM menu_categories WHERE id = ? AND restaurant_id = ?');
        $stmt->bind_param('ii', $categoryId, $restaurantId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ? (int) $row['id'] : null;
    }

    private static function blockIfExpired(array $restaurant, string $redirectTo): void
    {
        if ($restaurant['subscription_status'] === 'expired') {
            flash('error', 'Deneme/abonelik süreniz doldu. Düzenleme yapabilmek için planınızı yenilemeniz gerekir. Mevcut verileriniz siliniyor değil, sadece değişiklik yapamıyorsunuz.');
            redirect($redirectTo);
        }
    }

    private static function productCount(int $restaurantId): int
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT COUNT(*) AS c FROM menu_items WHERE restaurant_id = ?');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        return (int) $stmt->get_result()->fetch_assoc()['c'];
    }

    public static function dashboard(): void
    {
        $restaurant = self::guard();
        $productCount = self::productCount((int) $restaurant['id']);
        $trial = currentTrialInfo($restaurant);
        view('admin/dashboard', [
            'title' => 'Panelim - QR Menü',
            'restaurant' => $restaurant,
            'productCount' => $productCount,
            'trial' => $trial,
            'menuUrl' => menuUrl($restaurant['slug']),
            'success' => flash('success'),
        ]);
    }

    public static function products(): void
    {
        $restaurant = self::guard();
        $db = Database::get();
        $stmt = $db->prepare(
            'SELECT mi.*, mc.name AS category_name FROM menu_items mi
             LEFT JOIN menu_categories mc ON mc.id = mi.category_id AND mc.restaurant_id = mi.restaurant_id
             WHERE mi.restaurant_id = ? ORDER BY mi.sort_order ASC, mi.id DESC'
        );
        $stmt->bind_param('i', $restaurant['id']);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        view('admin/products', [
            'title' => 'Ürünler - QR Menü',
            'restaurant' => $restaurant,
            'items' => $items,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function newProductForm(): void
    {
        $restaurant = self::guard();
        $productCount = self::productCount((int) $restaurant['id']);
        if ($restaurant['max_products'] !== null && $productCount >= (int) $restaurant['max_products']) {
            flash('error', 'Planınızın ürün limitine ulaştınız (' . $restaurant['max_products'] . '). Daha fazla ürün eklemek için planınızı yükseltin.');
            redirect('/admin/products');
        }
        $categories = self::categories((int) $restaurant['id']);
        view('admin/product_form', [
            'title' => 'Yeni Ürün - QR Menü',
            'restaurant' => $restaurant,
            'categories' => $categories,
            'item' => null,
            'error' => flash('error'),
        ]);
    }

    private static function categories(int $restaurantId): array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM menu_categories WHERE restaurant_id = ? ORDER BY sort_order ASC');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5 MB

    private static function handleImageUpload(array $restaurant): ?string
    {
        if (!$restaurant['can_upload_images']) {
            return null;
        }
        $restaurantId = (int) $restaurant['id'];
        if (empty($_FILES['image']['name']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($_FILES['image']['size'] > self::MAX_UPLOAD_BYTES) {
            return null;
        }
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            return null;
        }
        $filename = uniqid('item_', true) . '.' . $allowed[$mime];
        return Storage::put($_FILES['image']['tmp_name'], $restaurantId . '/' . $filename, $mime);
    }

    public static function createProduct(): void
    {
        $restaurant = self::guard();
        self::blockIfExpired($restaurant, '/admin/products');
        $productCount = self::productCount((int) $restaurant['id']);
        if ($restaurant['max_products'] !== null && $productCount >= (int) $restaurant['max_products']) {
            flash('error', 'Ürün limitine ulaştınız.');
            redirect('/admin/products');
        }

        $name = trim($_POST['name'] ?? '');
        $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');
        $description = trim($_POST['description'] ?? '');
        $categoryId = $restaurant['can_use_categories']
            ? self::resolveOwnedCategoryId((int) ($_POST['category_id'] ?? 0) ?: null, (int) $restaurant['id'])
            : null;
        $isFeatured = ($restaurant['can_feature_products'] && isset($_POST['is_featured'])) ? 1 : 0;

        if ($name === '') {
            flash('error', 'Ürün adı zorunlu.');
            redirect('/admin/products/new');
        }

        $imagePath = self::handleImageUpload($restaurant);

        $db = Database::get();
        $stmt = $db->prepare(
            'INSERT INTO menu_items (restaurant_id, category_id, name, description, price, image_path, is_available, is_featured)
             VALUES (?, ?, ?, ?, ?, ?, 1, ?)'
        );
        $stmt->bind_param('iissdsi', $restaurant['id'], $categoryId, $name, $description, $price, $imagePath, $isFeatured);
        $stmt->execute();

        flash('success', 'Ürün eklendi.');
        redirect('/admin/products');
    }

    public static function editProductForm(int $id): void
    {
        $restaurant = self::guard();
        $item = self::findOwnedItem($id, (int) $restaurant['id']);
        $categories = self::categories((int) $restaurant['id']);
        view('admin/product_form', [
            'title' => 'Ürünü Düzenle - QR Menü',
            'restaurant' => $restaurant,
            'categories' => $categories,
            'item' => $item,
            'error' => flash('error'),
        ]);
    }

    private static function findOwnedItem(int $id, int $restaurantId): array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM menu_items WHERE id = ? AND restaurant_id = ?');
        $stmt->bind_param('ii', $id, $restaurantId);
        $stmt->execute();
        $item = $stmt->get_result()->fetch_assoc();
        if (!$item) {
            redirect('/admin/products');
        }
        return $item;
    }

    public static function updateProduct(int $id): void
    {
        $restaurant = self::guard();
        self::blockIfExpired($restaurant, '/admin/products/' . $id . '/edit');
        $item = self::findOwnedItem($id, (int) $restaurant['id']);

        $name = trim($_POST['name'] ?? '');
        $price = (float) str_replace(',', '.', $_POST['price'] ?? '0');
        $description = trim($_POST['description'] ?? '');
        $categoryId = $restaurant['can_use_categories']
            ? self::resolveOwnedCategoryId((int) ($_POST['category_id'] ?? 0) ?: null, (int) $restaurant['id'])
            : null;
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $isFeatured = ($restaurant['can_feature_products'] && isset($_POST['is_featured'])) ? 1 : 0;

        if ($name === '') {
            flash('error', 'Ürün adı zorunlu.');
            redirect('/admin/products/' . $id . '/edit');
        }

        $imagePath = self::handleImageUpload($restaurant) ?? $item['image_path'];

        $db = Database::get();
        $stmt = $db->prepare(
            'UPDATE menu_items SET category_id = ?, name = ?, description = ?, price = ?, image_path = ?, is_available = ?, is_featured = ?
             WHERE id = ? AND restaurant_id = ?'
        );
        $stmt->bind_param('issdsiiii', $categoryId, $name, $description, $price, $imagePath, $isAvailable, $isFeatured, $id, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Ürün güncellendi.');
        redirect('/admin/products');
    }

    public static function deleteProduct(int $id): void
    {
        $restaurant = self::guard();
        self::blockIfExpired($restaurant, '/admin/products');
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM menu_items WHERE id = ? AND restaurant_id = ?');
        $stmt->bind_param('ii', $id, $restaurant['id']);
        $stmt->execute();
        flash('success', 'Ürün silindi.');
        redirect('/admin/products');
    }

    public static function categoriesPage(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_use_categories']) {
            redirect('/admin/products');
        }
        view('admin/categories', [
            'title' => 'Kategoriler - QR Menü',
            'restaurant' => $restaurant,
            'categories' => self::categories((int) $restaurant['id']),
            'error' => flash('error'),
            'success' => flash('success'),
        ]);
    }

    public static function createCategory(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_use_categories']) {
            redirect('/admin/products');
        }
        self::blockIfExpired($restaurant, '/admin/categories');
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            flash('error', 'Kategori adı zorunlu.');
            redirect('/admin/categories');
        }
        $db = Database::get();
        $maxSort = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) AS m FROM menu_categories WHERE restaurant_id = ?');
        $maxSort->bind_param('i', $restaurant['id']);
        $maxSort->execute();
        $nextSort = (int) $maxSort->get_result()->fetch_assoc()['m'] + 1;

        $stmt = $db->prepare('INSERT INTO menu_categories (restaurant_id, name, sort_order) VALUES (?, ?, ?)');
        $stmt->bind_param('isi', $restaurant['id'], $name, $nextSort);
        $stmt->execute();

        flash('success', 'Kategori eklendi.');
        redirect('/admin/categories');
    }

    public static function deleteCategory(int $id): void
    {
        $restaurant = self::guard();
        self::blockIfExpired($restaurant, '/admin/categories');
        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM menu_categories WHERE id = ? AND restaurant_id = ?');
        $stmt->bind_param('ii', $id, $restaurant['id']);
        $stmt->execute();
        flash('success', 'Kategori silindi.');
        redirect('/admin/categories');
    }

    public static function contactForm(): void
    {
        $restaurant = self::guard();
        view('admin/contact', [
            'title' => 'İletişim - QR Menü',
            'restaurant' => $restaurant,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function updateContact(): void
    {
        $restaurant = self::guard();
        self::blockIfExpired($restaurant, '/admin/contact');
        $phone = trim($_POST['contact_phone'] ?? '');
        $address = trim($_POST['contact_address'] ?? '');
        $instagram = trim($_POST['contact_instagram'] ?? '');
        $whatsapp = trim($_POST['contact_whatsapp'] ?? '');
        $facebook = trim($_POST['contact_facebook'] ?? '');
        $x = trim($_POST['contact_x'] ?? '');

        $db = Database::get();
        $stmt = $db->prepare(
            'UPDATE restaurants SET contact_phone = ?, contact_address = ?, contact_instagram = ?, contact_whatsapp = ?, contact_facebook = ?, contact_x = ? WHERE id = ?'
        );
        $stmt->bind_param('ssssssi', $phone, $address, $instagram, $whatsapp, $facebook, $x, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Bilgiler güncellendi.');
        redirect('/admin/contact');
    }

    public static function toggleOpen(): void
    {
        $restaurant = self::guard();
        if ($restaurant['subscription_status'] === 'expired') {
            redirect('/admin');
        }
        $newState = $restaurant['is_open'] ? 0 : 1;
        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET is_open = ? WHERE id = ?');
        $stmt->bind_param('ii', $newState, $restaurant['id']);
        $stmt->execute();
        redirect('/admin');
    }

    public static function cancelMembership(): void
    {
        $restaurant = self::guard();
        if (in_array($restaurant['subscription_status'], ['canceled', 'expired'], true)) {
            redirect('/admin');
        }
        $db = Database::get();
        $stmt = $db->prepare("UPDATE restaurants SET subscription_status = 'canceled', is_open = 0 WHERE id = ?");
        $stmt->bind_param('i', $restaurant['id']);
        $stmt->execute();
        flash('success', 'Üyeliğiniz iptal edildi. Menünüz artık müşterilere görünmüyor.');
        redirect('/admin');
    }

    public static function analytics(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_view_analytics']) {
            redirect('/admin');
        }
        $db = Database::get();
        $restaurantId = (int) $restaurant['id'];

        $totalStmt = $db->prepare('SELECT COUNT(*) AS c FROM menu_visits WHERE restaurant_id = ?');
        $totalStmt->bind_param('i', $restaurantId);
        $totalStmt->execute();
        $total = (int) $totalStmt->get_result()->fetch_assoc()['c'];

        $todayStmt = $db->prepare('SELECT COUNT(*) AS c FROM menu_visits WHERE restaurant_id = ? AND DATE(visited_at) = CURDATE()');
        $todayStmt->bind_param('i', $restaurantId);
        $todayStmt->execute();
        $today = (int) $todayStmt->get_result()->fetch_assoc()['c'];

        $weekStmt = $db->prepare('SELECT COUNT(*) AS c FROM menu_visits WHERE restaurant_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
        $weekStmt->bind_param('i', $restaurantId);
        $weekStmt->execute();
        $week = (int) $weekStmt->get_result()->fetch_assoc()['c'];

        $dailyStmt = $db->prepare(
            'SELECT DATE(visited_at) AS day, COUNT(*) AS c FROM menu_visits
             WHERE restaurant_id = ? AND visited_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(visited_at) ORDER BY day ASC'
        );
        $dailyStmt->bind_param('i', $restaurantId);
        $dailyStmt->execute();
        $daily = $dailyStmt->get_result()->fetch_all(MYSQLI_ASSOC);

        view('admin/analytics', [
            'title' => 'Analitik - QR Menü',
            'restaurant' => $restaurant,
            'total' => $total,
            'today' => $today,
            'week' => $week,
            'daily' => $daily,
        ]);
    }

    public static function qr(): void
    {
        $restaurant = self::guard();
        view('admin/qr', [
            'title' => 'QR Kodum - QR Menü',
            'restaurant' => $restaurant,
            'menuUrl' => menuUrl($restaurant['slug']),
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function aboutPage(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_upload_images']) {
            redirect('/admin');
        }
        $gallery = self::galleryFor((int) $restaurant['id']);
        view('admin/about', [
            'title' => 'Hakkımızda & Galeri - QR Menü',
            'restaurant' => $restaurant,
            'gallery' => $gallery,
            'galleryLimit' => self::GALLERY_LIMIT,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    private static function galleryFor(int $restaurantId): array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM restaurant_gallery WHERE restaurant_id = ? ORDER BY sort_order ASC, id ASC');
        $stmt->bind_param('i', $restaurantId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function updateAbout(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_upload_images']) {
            redirect('/admin');
        }
        self::blockIfExpired($restaurant, '/admin/about');

        $aboutText = trim($_POST['about_text'] ?? '');
        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET about_text = ? WHERE id = ?');
        $stmt->bind_param('si', $aboutText, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Hakkımızda yazısı güncellendi.');
        redirect('/admin/about');
    }

    public static function updateLogo(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_upload_images']) {
            redirect('/admin');
        }
        self::blockIfExpired($restaurant, '/admin/about');

        $logoPath = self::handleImageUpload($restaurant);
        if ($logoPath === null) {
            flash('error', 'Geçerli bir logo seçmediniz (jpg, png veya webp olmalı).');
            redirect('/admin/about');
        }

        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET logo_path = ? WHERE id = ?');
        $stmt->bind_param('si', $logoPath, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Logo güncellendi.');
        redirect('/admin/about');
    }

    private const GALLERY_LIMIT = 12;

    public static function addGalleryImage(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_upload_images']) {
            redirect('/admin');
        }
        self::blockIfExpired($restaurant, '/admin/about');

        $currentCount = count(self::galleryFor((int) $restaurant['id']));
        if ($currentCount >= self::GALLERY_LIMIT) {
            flash('error', 'En fazla ' . self::GALLERY_LIMIT . ' fotoğraf ekleyebilirsiniz. Yeni eklemek için önce birini silin.');
            redirect('/admin/about');
        }

        $imagePath = self::handleImageUpload($restaurant);
        if ($imagePath === null) {
            flash('error', 'Geçerli bir görsel seçmediniz (jpg, png veya webp olmalı).');
            redirect('/admin/about');
        }

        $db = Database::get();
        $maxSort = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) AS m FROM restaurant_gallery WHERE restaurant_id = ?');
        $maxSort->bind_param('i', $restaurant['id']);
        $maxSort->execute();
        $nextSort = (int) $maxSort->get_result()->fetch_assoc()['m'] + 1;

        $stmt = $db->prepare('INSERT INTO restaurant_gallery (restaurant_id, image_path, sort_order) VALUES (?, ?, ?)');
        $stmt->bind_param('isi', $restaurant['id'], $imagePath, $nextSort);
        $stmt->execute();

        flash('success', 'Fotoğraf eklendi.');
        redirect('/admin/about');
    }

    public static function deleteGalleryImage(int $id): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_upload_images']) {
            redirect('/admin');
        }
        self::blockIfExpired($restaurant, '/admin/about');

        $db = Database::get();
        $stmt = $db->prepare('DELETE FROM restaurant_gallery WHERE id = ? AND restaurant_id = ?');
        $stmt->bind_param('ii', $id, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Fotoğraf silindi.');
        redirect('/admin/about');
    }

    public static function updateQr(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_customize_theme']) {
            redirect('/admin/qr');
        }
        self::blockIfExpired($restaurant, '/admin/qr');

        $color = $_POST['qr_color'] ?? '#24201d';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = '#24201d';
        }

        $logoPath = $restaurant['qr_logo_path'];
        if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK && $_FILES['logo']['size'] <= self::MAX_UPLOAD_BYTES) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            $mime = mime_content_type($_FILES['logo']['tmp_name']);
            if (isset($allowed[$mime])) {
                $filename = 'qrlogo_' . uniqid() . '.' . $allowed[$mime];
                $uploaded = Storage::put($_FILES['logo']['tmp_name'], $restaurant['id'] . '/' . $filename, $mime);
                if ($uploaded !== null) {
                    $logoPath = $uploaded;
                }
            }
        }

        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET qr_color = ?, qr_logo_path = ? WHERE id = ?');
        $stmt->bind_param('ssi', $color, $logoPath, $restaurant['id']);
        $stmt->execute();

        flash('success', 'QR görünümü güncellendi.');
        redirect('/admin/qr');
    }

    public static function paymentPage(): void
    {
        $restaurant = self::guard();
        $needsBillingInfo = !$restaurant['billing_identity_number'] || !$restaurant['billing_city'] || !$restaurant['contact_phone'] || !$restaurant['contact_address'];
        view('admin/payment', [
            'title' => 'Ödeme - QR Menü',
            'restaurant' => $restaurant,
            'needsBillingInfo' => $needsBillingInfo,
            'success' => flash('success'),
            'error' => flash('error'),
        ]);
    }

    public static function updateBillingInfo(): void
    {
        $restaurant = self::guard();
        $identity = trim($_POST['billing_identity_number'] ?? '');
        $city = trim($_POST['billing_city'] ?? '');
        $phone = trim($_POST['contact_phone'] ?? '');
        $address = trim($_POST['contact_address'] ?? '');

        if (!preg_match('/^\d{10,11}$/', $identity) || $city === '' || $phone === '' || $address === '') {
            flash('error', 'Lütfen TC kimlik/vergi no (10-11 haneli), şehir, telefon ve adres bilgilerini eksiksiz girin.');
            redirect('/admin/payment');
        }

        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET billing_identity_number = ?, billing_city = ?, contact_phone = ?, contact_address = ? WHERE id = ?');
        $stmt->bind_param('ssssi', $identity, $city, $phone, $address, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Fatura bilgileriniz kaydedildi. Şimdi kartınızı ekleyebilirsiniz.');
        redirect('/admin/payment');
    }

    public static function startCardCheckout(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['billing_identity_number'] || !$restaurant['billing_city']) {
            flash('error', 'Kart eklemeden önce fatura bilgilerinizi doldurmalısınız.');
            redirect('/admin/payment');
        }

        $plan = self::planById((int) $restaurant['plan_id']);
        $callbackUrl = (str_starts_with(menuUrl($restaurant['slug']), 'https') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/admin/payment/callback';
        $result = Iyzico::initCheckoutForm($restaurant, (float) $plan['price_monthly'], $callbackUrl);

        if (($result['status'] ?? '') !== 'success' || empty($result['paymentPageUrl'])) {
            flash('error', 'Ödeme sayfası başlatılamadı: ' . ($result['errorMessage'] ?? 'Bilinmeyen hata'));
            redirect('/admin/payment');
        }

        header('Location: ' . $result['paymentPageUrl']);
        exit;
    }

    private static function planById(int $planId): array
    {
        $db = Database::get();
        $stmt = $db->prepare('SELECT * FROM plans WHERE id = ?');
        $stmt->bind_param('i', $planId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function paymentCallback(): void
    {
        $restaurant = self::guard();
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        if ($token === '') {
            flash('error', 'Ödeme sonucu alınamadı (token yok).');
            redirect('/admin/payment');
        }

        $result = Iyzico::retrieveCheckoutForm($token);

        // Token başka bir restorana ait olabilir (ör. sızmış/tekrar kullanılmış token) - basketId
        // initCheckoutForm'da restoran id'sine bağlanmıştı, oturumdaki restoranla eşleşmiyorsa reddet.
        $expectedBasketId = 'sub-' . $restaurant['id'];
        if (($result['basketId'] ?? null) !== $expectedBasketId) {
            flash('error', 'Ödeme sonucu doğrulanamadı.');
            redirect('/admin/payment');
        }

        if (($result['status'] ?? '') !== 'success' || ($result['paymentStatus'] ?? 'SUCCESS') !== 'SUCCESS') {
            $db = Database::get();
            $log = $db->prepare('INSERT INTO payment_transactions (restaurant_id, amount, status, error_message) VALUES (?, ?, "failure", ?)');
            $amount = (float) ($result['paidPrice'] ?? 0);
            $errorMessage = $result['errorMessage'] ?? 'Ödeme başarısız';
            $log->bind_param('ids', $restaurant['id'], $amount, $errorMessage);
            $log->execute();
            flash('error', 'Ödeme başarısız oldu: ' . ($result['errorMessage'] ?? 'Bilinmeyen hata'));
            redirect('/admin/payment');
        }

        $cardUserKey = $result['cardUserKey'] ?? null;
        $cardToken = $result['cardToken'] ?? null;
        $paidPrice = (float) ($result['paidPrice'] ?? 0);
        $nextBillingAt = (new DateTime())->modify('+1 month')->format('Y-m-d H:i:s');

        $db = Database::get();
        $stmt = $db->prepare(
            "UPDATE restaurants SET iyzico_card_user_key = ?, iyzico_card_token = ?, subscription_status = 'active',
                next_billing_at = ?, payment_retry_count = 0 WHERE id = ?"
        );
        $stmt->bind_param('sssi', $cardUserKey, $cardToken, $nextBillingAt, $restaurant['id']);
        $stmt->execute();

        $log = $db->prepare('INSERT INTO payment_transactions (restaurant_id, amount, status, iyzico_payment_id) VALUES (?, ?, "success", ?)');
        $paymentId = $result['paymentId'] ?? null;
        $log->bind_param('ids', $restaurant['id'], $paidPrice, $paymentId);
        $log->execute();

        flash('success', 'Kartınız kaydedildi ve üyeliğiniz aktifleştirildi. Her ay otomatik olarak tahsilat yapılacak.');
        redirect('/admin/payment');
    }

    public static function updateTheme(): void
    {
        $restaurant = self::guard();
        if (!$restaurant['can_customize_theme']) {
            redirect('/admin/qr');
        }
        self::blockIfExpired($restaurant, '/admin/qr');

        $color = $_POST['theme_color'] ?? '';
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) {
            $color = null;
        }

        $db = Database::get();
        $stmt = $db->prepare('UPDATE restaurants SET theme_color = ? WHERE id = ?');
        $stmt->bind_param('si', $color, $restaurant['id']);
        $stmt->execute();

        flash('success', 'Menü sitenizin rengi güncellendi.');
        redirect('/admin/qr');
    }
}
