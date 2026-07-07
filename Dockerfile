FROM php:8.2-cli

RUN docker-php-ext-install mysqli \
    && apt-get update && apt-get install -y libzip-dev && rm -rf /var/lib/apt/lists/*

WORKDIR /app
COPY . /app

RUN mkdir -p /app/public/uploads && chmod -R 775 /app/public/uploads

ENV OM_ENV=production
EXPOSE 10000

CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public public/router.php"]
