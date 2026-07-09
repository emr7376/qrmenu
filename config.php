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
// her deploy'da yerel dosyalar silinir.
// ÖNCELİK 1: Cloudinary — kart istemez, sunucudan-sunucuya (server-to-server) yüklemeye izin verir.
// cloudinary.com'da ücretsiz hesap açınca Dashboard'da "Cloud name", "API Key", "API Secret" görünür.
define('OM_CLOUDINARY_CLOUD_NAME', getenv('OM_CLOUDINARY_CLOUD_NAME') ?: '');
define('OM_CLOUDINARY_API_KEY', getenv('OM_CLOUDINARY_API_KEY') ?: '');
define('OM_CLOUDINARY_API_SECRET', getenv('OM_CLOUDINARY_API_SECRET') ?: '');
// ÖNCELİK 2 (ARTIK KULLANILMIYOR — Render'ın datacenter IP'sini "forbidden" (kod 103) ile
// engelliyor, 2026-07-07'de canlıda tespit edildi). Kod yedek olarak duruyor, silinmedi.
define('OM_IMGBB_API_KEY', getenv('OM_IMGBB_API_KEY') ?: '');
// ÖNCELİK 3 (opsiyonel/yedek): Herhangi bir S3 UYUMLU servis (Cloudflare R2, Backblaze B2, vb.)
// bilgileri girilirse app/Storage.php görselleri oraya yükler.
// İkisi de boşsa (yerel geliştirme) eskisi gibi public/uploads klasörüne yazılır — sistem kırılmaz.
// OM_R2_ENDPOINT: tam host adresi. R2 için boş bırakılırsa OM_R2_ACCOUNT_ID'den otomatik
// üretilir ({account_id}.r2.cloudflarestorage.com). Backblaze B2 için tam endpoint'i
// (örn. s3.us-west-000.backblazeb2.com) doğrudan buraya yaz, ACCOUNT_ID'ye gerek yok.
define('OM_R2_ACCOUNT_ID', getenv('OM_R2_ACCOUNT_ID') ?: '');
define('OM_R2_ACCESS_KEY', getenv('OM_R2_ACCESS_KEY') ?: '');
define('OM_R2_SECRET_KEY', getenv('OM_R2_SECRET_KEY') ?: '');
define('OM_R2_BUCKET', getenv('OM_R2_BUCKET') ?: '');
define('OM_R2_ENDPOINT', getenv('OM_R2_ENDPOINT') ?: (OM_R2_ACCOUNT_ID !== '' ? OM_R2_ACCOUNT_ID . '.r2.cloudflarestorage.com' : ''));
define('OM_R2_REGION', getenv('OM_R2_REGION') ?: 'auto'); // R2 için "auto" kalsın; Backblaze B2 için gerçek bölge kodu (örn. us-west-000) gir
define('OM_R2_PUBLIC_URL', getenv('OM_R2_PUBLIC_URL') ?: ''); // örn. https://pub-xxxx.r2.dev veya B2'nin verdiği friendly URL

// iyzico (ödeme sağlayıcısı) — sandbox key'lerini https://sandbox-merchant.iyzipay.com üzerinden
// onay beklemeden hemen alabilirsin. Canlıya geçerken OM_IYZICO_BASE_URL'i production'a
// (https://api.iyzipay.com) çevirip gerçek (onaylı üye iş yeri) key'lerini gir.
define('OM_IYZICO_API_KEY', getenv('OM_IYZICO_API_KEY') ?: '');
define('OM_IYZICO_SECRET_KEY', getenv('OM_IYZICO_SECRET_KEY') ?: '');
define('OM_IYZICO_BASE_URL', getenv('OM_IYZICO_BASE_URL') ?: 'https://sandbox-api.iyzipay.com');

// Giriş yaparken e-postaya gönderilen tek kullanımlık doğrulama kodu (2FA) için.
// ÖNCELİK 1: Resend (resend.com) HTTP API — ücretsiz, Render'daki SMTP port engelini aşar.
// Domain doğrulanmadıysa SADECE Resend hesabının kendi sahibinin doğruladığı e-postaya
// gönderim yapılabilir (sandbox kısıtı) — geniş kullanım için ileride domain doğrulaması gerekir.
define('OM_RESEND_API_KEY', getenv('OM_RESEND_API_KEY') ?: '');
// ÖNCELİK 2 (alternatif): Brevo (brevo.com) HTTP API — ücretsiz (günde 300 e-posta), kart istemez.
// Brevo'da "Senders" bölümünden OM_SMTP_USER'daki adresi doğrulaman gerekir.
define('OM_BREVO_API_KEY', getenv('OM_BREVO_API_KEY') ?: '');
// ÖNCELİK 3 (yedek/yerel geliştirme): Gmail SMTP — composer/vendor yok, app/Mailer.php
// bağımsız ham soket ile gönderiyor. Render'ın ücretsiz katmanında ÇALIŞMIYOR (port engeli).
// OM_SMTP_USER: kodu gönderecek Gmail adresin.
// OM_SMTP_PASS: normal Gmail şifren DEĞİL — Google Hesabı > Güvenlik > 2 Adımlı Doğrulama açık olmalı,
// sonra "Uygulama Şifreleri" (App Passwords) ile üretilen 16 haneli özel şifre buraya girilir.
// İkisi de boşsa kod e-postayla gönderilmez, sadece log'a yazılır (geliştirme modu) — sistem kırılmaz.
define('OM_SMTP_HOST', getenv('OM_SMTP_HOST') ?: 'smtp.gmail.com');
define('OM_SMTP_PORT', (int) (getenv('OM_SMTP_PORT') ?: 587));
define('OM_SMTP_USER', getenv('OM_SMTP_USER') ?: '');
define('OM_SMTP_PASS', getenv('OM_SMTP_PASS') ?: '');
define('OM_SMTP_FROM_NAME', 'QRMenü');

// Render gibi platformlarda "production" set edilir — o zaman hata detayları ekrana basılmaz (güvenlik).
define('OM_IS_PRODUCTION', getenv('OM_ENV') === 'production');

// Ücretsiz dış cron pinger (örn. cron-job.org) ile GET /cron/charge-subscriptions?key=... tetiklemek için.
// Render'ın ücretsiz katmanında gerçek sunucu cron'u olmadığından bu yol kullanılıyor.
// Boşsa endpoint her zaman 403 döner (varsayılan olarak devre dışı, güvenli).
define('OM_CRON_SECRET', getenv('OM_CRON_SECRET') ?: '');

if (session_status() === PHP_SESSION_NONE) {
    // iyzico'nun hosted ödeme sayfasından POST ile dönen tek cross-site istek (/admin/payment/callback)
    // olduğu için SameSite=Strict kullanılmıyor; Lax modern tarayıcılarda zaten varsayılan davranış.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => OM_IS_PRODUCTION,
        'samesite' => 'Lax',
    ]);
    // Session'lar dosya yerine DB'de tutulur — Render'ın ücretsiz katmanında container
    // uyuyup yeniden başladığında yerel disk sıfırlanıyor, bu da rastgele "oturum zaman
    // aşımı" (CSRF uyuşmazlığı) ve giriş sonrası tekrar login'e atılma hatalarına sebep
    // oluyordu (2026-07-09). Bkz. app/DbSessionHandler.php ve schema.sql'deki sessions tablosu.
    require_once OM_ROOT . '/app/Database.php';
    require_once OM_ROOT . '/app/DbSessionHandler.php';
    session_set_save_handler(new DbSessionHandler(), true);
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', OM_IS_PRODUCTION ? '0' : '1');
