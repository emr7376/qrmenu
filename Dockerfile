FROM php:8.2-cli

RUN docker-php-ext-install mysqli opcache \
    && apt-get update && apt-get install -y libzip-dev && rm -rf /var/lib/apt/lists/*

# OPcache: php -S built-in server yeni bir istek her geldiğinde script'leri diskten
# okuyup baştan derliyordu (opcache hiç açık değildi) - her sayfa yüklemesine gereksiz
# bir derleme maliyeti ekliyordu. validate_timestamps açık bırakıldı (deploy'da dosyalar
# değişebiliyor, prod'da kısa bir stale-cache riski deploy başına bir kaç saniyeyi aşmaz).
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'opcache.memory_consumption=64'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=1'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'zlib.output_compression=1'; \
    } > /usr/local/etc/php/conf.d/perf.ini

WORKDIR /app
COPY . /app

RUN mkdir -p /app/public/uploads && chmod -R 775 /app/public/uploads

ENV OM_ENV=production
# php -S varsayılan olarak istekleri TEK bir process'te sırayla işler - bir sayfanın
# CSS/JS/görsel isteklerinin bile birbirinin arkasında kuyruklanmasına (ve eşzamanlı
# ziyaretçilerin birbirini bloklamasına) sebep oluyordu. PHP_CLI_SERVER_WORKERS bu
# built-in sunucuyu birden fazla worker process ile çalıştırır (PHP 8.0+).
ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/router.php"]
