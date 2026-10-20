FROM php:8.3-cli

# تثبيت الملحقات الأساسية
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev libsqlite3-dev zip curl \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring gd

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# نسخ باقي ملفات المشروع
COPY . .

# تثبيت الحزم وإعطاء الصلاحيات
RUN composer install --no-dev --optimize-autoloader
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 8080

# تشغيل سيرفر PHP المدمج على المنفذ 8080 مباشرة
CMD ["php", "-S", "0.0.0.0:8080", "-t", "public"]
