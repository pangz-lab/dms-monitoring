#COPY apache2/000-default.conf /etc/apache2/sites-enable/000-default.conf
FROM php:7.3-apache
ENV APACHE_LOG_DIR=/usr/local/log

COPY . /var/www/html/

WORKDIR /var/www/html/
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
    composer dump-autoload
#    mkdir $APACHE_LOG_DIR \
#    chmod 0775 $APACHE_LOG_DIR
#apt-get update && \
#apt-get install vim && \

EXPOSE 8181:80
