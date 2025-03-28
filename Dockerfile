FROM php:8.2-apache

# Copy all contents to apache web root
COPY . /var/www/html/

# Set proper permissions for SQLite DB
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

EXPOSE 80