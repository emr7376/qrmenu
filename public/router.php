<?php
// PHP built-in server router: statik dosyaları doğrudan servis et, geri kalanı index.php'ye yönlendir.
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $path;
if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    // Statik dosyalar (assets/uploads) tarayıcı tarafında uzun süre cache'lensin - built-in
    // sunucu varsayılan olarak hiçbir Cache-Control göndermiyordu, bu da her sayfa
    // ziyaretinde CSS/JS/görsellerin ağdan tekrar tekrar çekilmesine sebep oluyordu.
    header('Cache-Control: public, max-age=86400');
    return false;
}
require __DIR__ . '/index.php';
