FROM php:8.2-apache

# Install and enable mysqli & pdo_mysql extensions
RUN docker-php-ext-install mysqli pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files into the Apache document root
COPY . /var/www/html/

# Set proper web permissions
RUN chown -R www-data:www-data /var/www/html

# Configure Apache to listen on Railway's dynamic $PORT
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 80
CMD ["apache2-foreground"]