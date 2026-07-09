<?php

// Session'ı sadece gerçekten gerekiyorsa başlat (bkz. config.php). CSRF/flash/auth hiçbiri
// public menü/anasayfa GET'lerinde kullanılmıyor - bu istekler sitedeki tüm trafiğin büyük
// çoğunluğunu oluşturduğu için her birinde session'a (dolayısıyla DB'ye) dokunmamak
// ölçülebilir bir hız kazancı sağlıyor. Diğer her rota (admin/login/superadmin/onboarding,
// tüm POST'lar) eskisi gibi session'ı eager başlatıyor.
$omMethod = $_SERVER['REQUEST_METHOD'];
$omPath = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';
$omSessionFreePath = $omPath === '/' || preg_match('#^/menu/[a-z0-9-]+(/(hakkimizda|menu|konum))?$#', $omPath);
define('OM_SKIP_SESSION', $omMethod === 'GET' && $omSessionFreePath);

require __DIR__ . '/../config.php';
require_once OM_ROOT . '/app/Database.php';
require OM_ROOT . '/app/Storage.php';
require OM_ROOT . '/app/Auth.php';
require OM_ROOT . '/app/Mailer.php';
require OM_ROOT . '/app/helpers.php';
require OM_ROOT . '/app/i18n.php';
require OM_ROOT . '/app/controllers/SiteController.php';
require OM_ROOT . '/app/controllers/AuthController.php';
require OM_ROOT . '/app/Iyzico.php';
require OM_ROOT . '/app/controllers/AdminController.php';
require OM_ROOT . '/app/controllers/MenuController.php';
require OM_ROOT . '/app/OwnerAuth.php';
require OM_ROOT . '/app/controllers/OwnerController.php';
require OM_ROOT . '/app/SubscriptionBiller.php';
require OM_ROOT . '/app/controllers/CronController.php';
require OM_ROOT . '/app/controllers/OnboardingController.php';

$method = $_SERVER['REQUEST_METHOD'];
// HEAD istekleri (UptimeRobot gibi izleme servislerinin kullandığı) GET route'larıyla
// eşleştirilir; gövde çıktısı PHP'nin kendi SAPI davranışıyla zaten bastırılır.
$isHeadRequest = $method === 'HEAD';
if ($isHeadRequest) {
    $method = 'GET';
}
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = rtrim($path, '/');
if ($path === '') {
    $path = '/';
}

$routes = [
    'GET' => [
        '#^/$#' => [SiteController::class, 'home'],
        '#^/login$#' => [AuthController::class, 'showLogin'],
        '#^/login/verify$#' => [AuthController::class, 'showVerify'],
        '#^/logout$#' => [AuthController::class, 'logout'],
        '#^/admin$#' => [AdminController::class, 'dashboard'],
        '#^/admin/onboarding$#' => [OnboardingController::class, 'show'],
        '#^/admin/onboarding/finish$#' => [OnboardingController::class, 'finish'],
        '#^/admin/products$#' => [AdminController::class, 'products'],
        '#^/admin/products/new$#' => [AdminController::class, 'newProductForm'],
        '#^/admin/products/(\d+)/edit$#' => [AdminController::class, 'editProductForm'],
        '#^/admin/contact$#' => [AdminController::class, 'contactForm'],
        '#^/admin/categories$#' => [AdminController::class, 'categoriesPage'],
        '#^/admin/analytics$#' => [AdminController::class, 'analytics'],
        '#^/admin/about$#' => [AdminController::class, 'aboutPage'],
        '#^/admin/qr$#' => [AdminController::class, 'qr'],
        '#^/admin/payment$#' => [AdminController::class, 'paymentPage'],
        '#^/admin/payment/callback$#' => [AdminController::class, 'paymentCallback'],
        '#^/admin/plan$#' => [AdminController::class, 'planPage'],
        '#^/menu/([a-z0-9-]+)$#' => [MenuController::class, 'home'],
        '#^/menu/([a-z0-9-]+)/hakkimizda$#' => [MenuController::class, 'about'],
        '#^/menu/([a-z0-9-]+)/menu$#' => [MenuController::class, 'menuPage'],
        '#^/menu/([a-z0-9-]+)/konum$#' => [MenuController::class, 'location'],
        '#^/superadmin/login$#' => [OwnerController::class, 'showLogin'],
        '#^/superadmin/logout$#' => [OwnerController::class, 'logout'],
        '#^/superadmin$#' => [OwnerController::class, 'dashboard'],
        '#^/superadmin/plans$#' => [OwnerController::class, 'plans'],
        '#^/superadmin/database$#' => [OwnerController::class, 'database'],
        '#^/superadmin/restaurants/new$#' => [OwnerController::class, 'newRestaurantForm'],
        '#^/cron/charge-subscriptions$#' => [CronController::class, 'chargeSubscriptions'],
    ],
    'POST' => [
        '#^/login$#' => [AuthController::class, 'login'],
        '#^/login/verify$#' => [AuthController::class, 'verifyCode'],
        '#^/login/verify/resend$#' => [AuthController::class, 'resendCode'],
        '#^/signup$#' => [AuthController::class, 'signup'],
        '#^/admin/onboarding/product$#' => [OnboardingController::class, 'addFirstProduct'],
        '#^/admin/products/new$#' => [AdminController::class, 'createProduct'],
        '#^/admin/products/(\d+)/edit$#' => [AdminController::class, 'updateProduct'],
        '#^/admin/products/(\d+)/delete$#' => [AdminController::class, 'deleteProduct'],
        '#^/admin/contact$#' => [AdminController::class, 'updateContact'],
        '#^/admin/categories$#' => [AdminController::class, 'createCategory'],
        '#^/admin/categories/(\d+)/delete$#' => [AdminController::class, 'deleteCategory'],
        '#^/admin/toggle$#' => [AdminController::class, 'toggleOpen'],
        '#^/admin/cancel-membership$#' => [AdminController::class, 'cancelMembership'],
        '#^/admin/about$#' => [AdminController::class, 'updateAbout'],
        '#^/admin/logo$#' => [AdminController::class, 'updateLogo'],
        '#^/admin/gallery$#' => [AdminController::class, 'addGalleryImage'],
        '#^/admin/gallery/(\d+)/delete$#' => [AdminController::class, 'deleteGalleryImage'],
        '#^/admin/qr$#' => [AdminController::class, 'updateQr'],
        '#^/admin/theme$#' => [AdminController::class, 'updateTheme'],
        '#^/admin/billing-info$#' => [AdminController::class, 'updateBillingInfo'],
        '#^/admin/payment/start$#' => [AdminController::class, 'startCardCheckout'],
        '#^/admin/payment/callback$#' => [AdminController::class, 'paymentCallback'],
        '#^/admin/plan$#' => [AdminController::class, 'updatePlanSelf'],
        '#^/superadmin/login$#' => [OwnerController::class, 'login'],
        '#^/superadmin/restaurants/new$#' => [OwnerController::class, 'createRestaurant'],
        '#^/superadmin/restaurants/(\d+)$#' => [OwnerController::class, 'updateRestaurant'],
        '#^/superadmin/plans/(\d+)$#' => [OwnerController::class, 'updatePlan'],
    ],
];

if ($method === 'GET' && $path === '/signup') {
    redirect('/login');
}

// iyzico'nun kendi sunucusundan POST ile dönen tek callback - bizim formumuzdan gelmediği için CSRF token'ı yok.
$csrfExemptPaths = ['/admin/payment/callback'];
if ($method === 'POST' && !in_array($path, $csrfExemptPaths, true) && !csrfValid()) {
    http_response_code(419);
    echo 'Oturumunuz zaman aşımına uğradı, lütfen sayfayı yenileyip tekrar deneyin.';
    exit;
}

foreach ($routes[$method] ?? [] as $pattern => $handler) {
    if (preg_match($pattern, $path, $matches)) {
        array_shift($matches);
        [$class, $methodName] = $handler;
        call_user_func_array([$class, $methodName], $matches);
        exit;
    }
}

http_response_code(404);
echo '404 - Sayfa bulunamadı';
