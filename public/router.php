<?php
// PHP built-in server router: statik dosyaları doğrudan servis et, geri kalanı index.php'ye yönlendir.
$path = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $path;
if ($path !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}
require __DIR__ . '/index.php';
