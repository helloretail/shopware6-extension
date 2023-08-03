FROM debian:12-slim as build

WORKDIR /usr/app/src

RUN apt update && \
    apt install curl php php-fpm supervisor screen -y

RUN apt install -y composer php-curl apache2 php-dom php-fileinfo php-gd php-iconv php-intl php-json php-xml php-mbstring pcregrep php-pdo php-mysql php-phar php-simplexml php-xml php-zip zlib1g-dev vim && a2enmod rewrite

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR "/var/log/apache"
ENV CFLAGS "-DBIG_SECURITY_HOLE"
ENV APACHE_PID_FILE /var/run/apache2/apache2$SUFFIX.pid
COPY docker-config/shopware6/shopware-installer.phar.php .
COPY docker-config/shopware6/virtualhosts.conf /etc/apache2/sites-available/shopware.hrdev.test.conf
RUN ln -s /etc/apache2/sites-available/shopware.hrdev.test.conf /etc/apache2/sites-enabled/
RUN sed -i "s/;opcache.memory_consumption=128/opcache.memory_consumption=256/g" /etc/php/8.2/apache2/php.ini
RUN sed -i "s/memory_limit = 128M/memory_limit = 512M/g" /etc/php/8.2/apache2/php.ini
RUN chown -R www-data:www-data /usr/app/src && chown -R www-data:www-data /var/log/apache2 && chown -R www-data:www-data /var/run/apache2
RUN touch /var/log/DEV.log && touch /var/log/PROD.log && chown www-data:www-data /var/log/PROD.log && chown www-data:www-data /var/log/DEV.log

CMD ["apache2ctl", "-DFOREGROUND"]

FROM build

COPY --chown=www-data:www-data exports/shopware /usr/app/src
COPY --chown=www-data:www-data docker-config/shopware6/entrypoint.sh .
RUN chown -R www-data:www-data /usr/app/src

USER www-data:www-data

ENTRYPOINT ["./entrypoint.sh"]
CMD ["apache2ctl", "-DFOREGROUND"]