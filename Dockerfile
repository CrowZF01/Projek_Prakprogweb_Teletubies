FROM php:8.2-apache

# Install extension mysqli yang wajib ada untuk koneksi database proyekmu
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Salin seluruh file proyek ke folder server Apache di dalam container
COPY . /var/www/html/

# Berikan hak akses yang benar agar Apache bisa membaca file proyek
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80