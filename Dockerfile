FROM php:8.3-apache

# تثبيت الإضافات اللازمة لـ Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# تفعيل مود الـ Rewrite في Apache
RUN a2enmod rewrite

# تعديل المجلد الرئيسي ليكون داخل public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# تثبيت Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# نسخ ملفات المشروع داخل السيرفر
COPY . /var/www/html

# تثبيت الحزم وإعطاء الصلاحيات الكاملة لمجلد التخزين وقاعدة البيانات
RUN composer install --no-interaction --optimize-autoloader --no-dev

# إعطاء صلاحيات الكتابة لملفات قاعدة البيانات ومجلد التخزين
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# تثبيت تعريف MySQL لـ PHP
RUN docker-php-ext-install pdo_mysql

EXPOSE 80

# تشغيل أمر الميجريشن تلقائياً ثم بدء تشغيل خادم Apache
CMD php artisan migrate --force && apache2-foreground