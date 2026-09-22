# 1. الصورة الأساسية (PHP 8.2 مع FPM)
FROM php:8.3-fpm
# 2. تثبيت الحزم ومكتبات النظام الأساسية
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# 3. تثبيت امتدادات PHP التي يحتاجها Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# 4. تثبيت Composer داخل الحاوية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. تحديد مجلد العمل
WORKDIR /var/www

# 6. نسخ ملفات المشروع
COPY . /var/www

# 7. تثبيت مكتبات Composer
RUN composer install --no-interaction --optimize-autoloader --no-dev

# 8. إعطاء الصلاحيات لمجلدات الـ Cache والـ Storage
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]