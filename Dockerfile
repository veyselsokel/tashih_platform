# PHP 8.2 ve FPM (PHP'nin web sunucularıyla konuşma şekli) içeren temel bir kutu kullan
FROM php:8.2-fpm-alpine

# Çalışma klasörünü ayarla
WORKDIR /var/www/html

# Gerekli Linux paketlerini ve PHP eklentilerini kur
# Bu komutlar, PHP projenizin ihtiyaç duyduğu şeyleri (veritabanı bağlantısı, resim işleme vb.) yükler
RUN apk add --no-cache \
    libzip-dev zip unzip \
    libpng-dev libjpeg-turbo-dev freetype-dev \
    icu-dev \
    libxml2-dev \
    mysql-client \
    nodejs npm yarn # Vue.js için Node.js ve Yarn
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    pdo pdo_mysql zip exif pcntl intl opcache

# Composer'ı (PHP paket yöneticisi) kur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Proje dosyalarını kutuya kopyala (docker-compose.yml'deki volume mount olmasa da çalışsın diye)
COPY . .

# Projenizin PHP bağımlılıklarını (vendor klasörü) kur
RUN composer install --optimize-autoloader --no-dev --no-interaction --no-progress
# Projenizin JavaScript bağımlılıklarını (node_modules) kur ve Vue.js projesini derle
RUN yarn install --frozen-lockfile
RUN yarn build

# Dosya izinlerini ayarla (web sunucusunun yazabilmesi için)
RUN chown -R www-data:www-data storage bootstrap/cache public/storage
RUN chmod -R 775 storage bootstrap/cache public/storage

# PHP-FPM'i çalıştır (uygulama kutusunun ana görevi)
CMD ["php-fpm"]