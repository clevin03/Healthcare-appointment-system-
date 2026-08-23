FROM php:8.2-fpm-alpine

# Install Nginx and required PHP extensions
RUN apk add --no-cache nginx \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable mysqli pdo_mysql

# Create necessary directories for Nginx and PHP
RUN mkdir -p /run/nginx /var/www/html

# Copy application files
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html

# Create lightweight Nginx configuration that dynamically binds to Railway's $PORT
RUN echo 'server { \
    listen 80 default_server; \
    listen [::]:80 default_server; \
    root /var/www/html; \
    index index.php index.html; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_index index.php; \
        include fastcgi_params; \
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; \
    } \
}' > /etc/nginx/http.d/default.conf

# Startup script to replace the port dynamically and start both PHP-FPM and Nginx
CMD sh -c "sed -i 's/80/'\"\$PORT\"'/g' /etc/nginx/http.d/default.conf && php-fpm -D && nginx -g 'daemon off;'"