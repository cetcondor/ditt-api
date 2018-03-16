FROM            php:7.1-fpm


########################
# Install dependencies #
########################
RUN             apt-get update \
                && apt-get upgrade -y \
                && apt-get install -y --no-install-recommends \
                    fontconfig \
                    g++ \
                    libfreetype6 \
                    libicu-dev \
                    libjpeg-dev \
                    libpng12-dev \
                    postgresql-server-dev-all \
                && rm -rf /var/lib/apt/lists/* \
                && docker-php-ext-install exif \
                && docker-php-ext-configure gd --with-jpeg-dir=/usr/ \
                && docker-php-ext-install gd \
                && docker-php-ext-install intl \
                && docker-php-ext-install mbstring \
                && docker-php-ext-install pdo_pgsql \
                && pecl install xdebug


####################
# Add config files #
####################
COPY            ./php.ini /usr/local/etc/php/php.ini
COPY            ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY            ./init-container.sh /root/init-container.sh


#######
# Run #
#######
CMD             bash /root/init-container.sh /www && php-fpm
