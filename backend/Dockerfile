FROM php:8.3-cli

WORKDIR /app

# Dependencias del sistema + extensiones PHP requeridas por Laravel
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd mbstring xml bcmath tokenizer ctype \
    && rm -rf /var/lib/apt/lists/*

# Xdebug: solo se instala si XDEBUG_INSTALL=true (desarrollo local)
ARG XDEBUG_INSTALL=false
RUN if [ "$XDEBUG_INSTALL" = "true" ]; then \
        pecl install xdebug \
        && docker-php-ext-enable xdebug \
        && echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; \
    fi

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código fuente
COPY . /app

# Permisos de escritura en storage y bootstrap/cache
RUN mkdir -p /app/storage/logs \
             /app/storage/framework/sessions \
             /app/storage/framework/views \
             /app/storage/framework/cache \
             /app/bootstrap/cache \
    && chmod -R 775 /app/storage /app/bootstrap/cache

# Instalar dependencias PHP (sin dev en producción)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# Puerto que expone php artisan serve
EXPOSE 8000

# Arranque: migrar + servir
# migrate --force es idempotente: si las tablas ya existen, no hace nada
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan migrate --force && \
    php artisan serve --host=0.0.0.0 --port=8000