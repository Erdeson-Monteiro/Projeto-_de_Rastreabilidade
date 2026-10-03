# Apache + PHP com o driver do MySQL (equivalente ao XAMPP)
FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite expires deflate \
    && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf \
    && echo "date.timezone = America/Fortaleza" > /usr/local/etc/php/conf.d/timezone.ini

# Página inicial em http://localhost:8080 leva direto ao sistema
RUN echo '<?php header("Location: /projeto_rastreabilidade/");' > /var/www/html/index.php
