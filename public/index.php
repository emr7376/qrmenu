<?php

require __DIR__ . '/../config.php';
require OM_ROOT . '/app/Database.php';
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

$method = $_SERVER['REQUEST_METHOD'];
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
    ],
    'POST' => [
        '#^/login$#' => [AuthController::class, 'login'],
        '#^/login/verify$#' => [AuthController::class, 'verifyCode'],
        '#^/login/verify/resend$#' => [AuthController::class, 'resendCode'],
        '#^/signup$#' => [AuthController::class, 'signup'],
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
        '#^/superadmin/login$#' => [OwnerController::class, 'login'],
        '#^/superadmin/restaurants/new$#' => [OwnerController::class, 'createRestaurant'],
        '#^/superadmin/restaurants/(\d+)$#' => [OwnerController::class, 'updateRestaurant'],
        '#^/superadmin/plans/(\d+)$#' => [OwnerController::class, 'updatePlan'],
    ],
];

if ($method === 'GET' && $path === '/signup') {
    redirect('/login');
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
