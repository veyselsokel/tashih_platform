# Dockerfile

# Temel imaj olarak resmi PHP-FPM imajını kullan (Alpine sürümü daha küçüktür)
# Projenizin gerektirdiği PHP sürümünü kontrol edin!
FROM php:8.3-fpm-alpine

# Çalışma dizinini ayarla
WORKDIR /var/www/html

# İmaj içine yazılım kurarken önbellek kullanma (imaj boyutunu küçültür)
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_NO_INTERACTION=1

# Sistem bağımlılıklarını kur:
# build-base, $PHPIZE_DEVS: PHP eklentilerini derlemek için gerekli.
# curl, git, zip, unzip: Genel araçlar ve Composer için.
# libzip-dev, libpng-dev, jpeg-dev, freetype-dev, icu-dev: PHP eklentilerinin ihtiyaç duyduğu kütüphaneler.
# mariadb-dev: pdo_mysql eklentisi için Alpine'deki gerekli başlık dosyaları.
# Diğer eklentiler için farklı kütüphaneler gerekebilir (örn: postgresql-dev).
RUN apk update && apk add --no-cache \
    build-base \
    $PHPIZE_DEVS \
    curl \
    git \
    zip \
    unzip \
    libzip-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    icu-dev \
    mariadb-dev \
    # Eğer supervisor gibi bir process manager kullanacaksanız: supervisor
    && rm -rf /var/cache/apk/*

# Gerekli PHP eklentilerini kur:
# pdo_mysql: MySQL/MariaDB bağlantısı için.
# mbstring: Laravel'in çoklu byte karakterler için ihtiyacı var.
# exif: Resim meta verileri için.
# pcntl: Kuyruk (queue) işlemleri için (isteğe bağlı ama önerilir).
# gd: Resim işleme (yeniden boyutlandırma vb.) için (isteğe bağlı).
# zip: Zip arşivleri için.
# bcmath: Matematiksel işlemler için (bazen gerekir).
# intl: Uluslararasılaştırma için.
# Projenizin ihtiyacına göre başka eklentiler ekleyebilirsiniz (örn: redis).
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    gd \
    zip \
    bcmath \
    intl

# Composer'ı (PHP paket yöneticisi) global olarak kur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ----- Uygulama Kodu -----
# Kodun tamamını buraya KOPYALAMIYORUZ, çünkü docker-compose.yml içinde
# 'volumes' kullanarak yerel dizini konteynere bağlıyoruz. Bu, geliştirmeyi
# ve güncellemeyi kolaylaştırır. Eğer production imajı yapsaydık ve volume
# kullanmasaydık, burada 'COPY . .' komutu olurdu.

# ----- İzinler -----
# storage ve bootstrap/cache klasörlerinin izinlerini, konteyner çalıştırıldıktan
# ve volume bağlandıktan SONRA ayarlamak daha sağlıklıdır.
# Bu yüzden bu adımı burada atlıyoruz. (Konteynere girip 'chown' yapacağız)

# PHP-FPM'in çalışacağı 9000 portunu aç
EXPOSE 9000

# Konteyner başladığında çalıştırılacak varsayılan komut
CMD ["php-fpm"]

# İsteğe bağlı: Güvenlik için root olmayan kullanıcıya geçiş yapabiliriz.
# Bu imajda php-fpm varsayılan olarak www-data kullanıcısıyla çalışır.
# USER www-data
