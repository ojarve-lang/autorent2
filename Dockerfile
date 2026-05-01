FROM php:8.2-apache

RUN docker-php-ext-install mysqli

COPY . /var/www/html/

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

RUN echo 'Alias /admin /var/www/html/admin' >> /etc/apache2/apache2.conf && \
    echo '<Directory /var/www/html/admin>' >> /etc/apache2/apache2.conf && \
    echo 'Require all granted' >> /etc/apache2/apache2.conf && \
    echo '</Directory>' >> /etc/apache2/apache2.conf

RUN chown -R www-data:www-data /var/www/html
