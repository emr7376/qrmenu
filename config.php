<?php
// QR Menü SaaS - genel ayarlar
//
// Yerelde (Mac/Local by Flywheel) çalışırken hiçbir ortam değişkeni yoksa aşağıdaki
// sabit yerel değerler kullanılır. Render gibi bir sunucuya deploy edilince ortam
// değişkenleri (OM_DB_HOST, OM_DB_PORT, OM_DB_NAME, OM_DB_USER, OM_DB_PASS) set edilir
// ve bunlar otomatik olarak öncelik kazanır — kodda hiçbir değişiklik gerekmez.

define('OM_ROOT', __DIR__);

$omDbHost = getenv('OM_DB_HOST') ?: null; // örn. db4free.net — set edilmişse uzak sunucuya TCP ile bağlanılır
define('OM_DB_HOST', $omDbHost);
define('OM_DB_PORT', (int) (getenv('OM_DB_PORT') ?: 3306));
define('OM_DB_SOCKET', $omDbHost ? null : '/Users/apple/Library/Application Support/Local/run/DZwlmgSPz/mysql/mysqld.sock');
define('OM_DB_NAME', getenv('OM_DB_NAME') ?: 'onlinemenu');
define('OM_DB_USER', getenv('OM_DB_USER') ?: 'root');
define('OM_DB_PASS', getenv('OM_DB_PASS') ?: 'root');
define('OM_TRIAL_DAYS', 7);
define('OM_CONTACT_EMAIL', getenv('OM_CONTACT_EMAIL') ?: 'emirkucur58@gmail.com');
define('OM_DEMO_MENU_SLUG', 'basket'); // landing page'de "örnek menüyü gör" linki için
// Konum sayfasında "gerçek sürüş süresi" için Google Maps Distance Matrix API anahtarı.
// Google Cloud Console'da "Distance Matrix API" açık, faturalandırması aktif bir API key gerekir.
// Boş bırakılırsa sistem otomatik olarak kuş uçuşu mesafe + tahmini süreye düşer (ücretsiz, API gerektirmez).
define('OM_GOOGLE_MAPS_API_KEY', getenv('OM_GOOGLE_MAPS_API_KEY') ?: '');
define('OM_UPLOAD_DIR', OM_ROOT . '/public/uploads');
define('OM_UPLOAD_URL', '/uploads');

// Görsel depolama (logo/galeri/ürün fotoğrafları) — Render'da disk kalıcı olmadığı için
// her deploy'da yerel dosyalar silinir. Cloudflare R2 (S3 uyumlu, ücretsiz katmanı var)
// bilgileri girilirse app/Storage.php görselleri oraya yükler. Boş bırakılırsa (yerel
// geliştirme) eskisi gibi public/uploads klasörüne yazılır — sistem kırılmaz.
define('OM_R2_ACCOUNT_ID', getenv('OM_R2_ACCOUNT_ID') ?: '');
define('OM_R2_ACCESS_KEY', getenv('OM_R2_ACCESS_KEY') ?: '');
define('OM_R2_SECRET_KEY', getenv('OM_R2_SECRET_KEY') ?: '');
define('OM_R2_BUCKET', getenv('OM_R2_BUCKET') ?: '');
define('OM_R2_PUBLIC_URL', getenv('OM_R2_PUBLIC_URL') ?: ''); // örn. https://pub-xxxx.r2.dev veya kendi domainin

// iyzico (ödeme sağlayıcısı) — sandbox key'lerini https://sandbox-merchant.iyzipay.com üzerinden
// onay beklemeden hemen alabilirsin. Canlıya geçerken OM_IYZICO_BASE_URL'i production'a
// (https://api.iyzipay.com) çevirip gerçek (onaylı üye iş yeri) key'lerini gir.
define('OM_IYZICO_API_KEY', getenv('OM_IYZICO_API_KEY') ?: '');
define('OM_IYZICO_SECRET_KEY', getenv('OM_IYZICO_SECRET_KEY') ?: '');
define('OM_IYZICO_BASE_URL', getenv('OM_IYZICO_BASE_URL') ?: 'https://sandbox-api.iyzipay.com');

// Giriş yaparken e-postaya gönderilen tek kullanımlık doğrulama kodu (2FA) için
// ÜCRETSİZ Gmail SMTP kullanılıyor (composer/vendor yok, app/Mailer.php bağımlısız ham soket ile gönderiyor).
// OM_SMTP_USER: kodu gönderecek Gmail adresin.
// OM_SMTP_PASS: normal Gmail şifren DEĞİL — Google Hesabı > Güvenlik > 2 Adımlı Doğrulama açık olmalı,
// sonra "Uygulama Şifreleri" (App Passwords) ile üretilen 16 haneli özel şifre buraya girilir.
// Boş bırakılırsa kod e-postayla gönderilmez, sadece log'a yazılır (geliştirme modu) — sistem kırılmaz.
define('OM_SMTP_HOST', getenv('OM_SMTP_HOST') ?: 'smtp.gmail.com');
define('OM_SMTP_PORT', (int) (getenv('OM_SMTP_PORT') ?: 587));
define('OM_SMTP_USER', getenv('OM_SMTP_USER') ?: '');
define('OM_SMTP_PASS', getenv('OM_SMTP_PASS') ?: '');
define('OM_SMTP_FROM_NAME', 'QRMenü');

// Render gibi platformlarda "production" set edilir — o zaman hata detayları ekrana basılmaz (güvenlik).
define('OM_IS_PRODUCTION', getenv('OM_ENV') === 'production');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', OM_IS_PRODUCTION ? '0' : '1');
