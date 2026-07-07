#!/bin/bash
# Yerelde QR Menü SaaS uygulamasını çalıştırır.
PHP_BIN="/Users/apple/Library/Application Support/Local/lightning-services/php-8.2.29+0/bin/darwin-arm64/bin/php"
cd "$(dirname "$0")"
"$PHP_BIN" -S 0.0.0.0:8000 -t public public/router.php
