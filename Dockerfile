FROM php:8.2-apache

# 1. Install required MySQL extensions
RUN docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# 2. Hard-clean all existing MPM modules to avoid duplicates, then enable ONLY mpm_prefork & rewrite
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork rewrite

# 3. Configure Apache port binding for Railway's dynamic $PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

# 4. Copy project files
COPY . /var/www/html/

# 5. Set proper web permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]