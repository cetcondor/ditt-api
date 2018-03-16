FROM            php:7.1-fpm


########################
# Install dependencies #
########################
RUN             apt-get update \
                && apt-get upgrade -y \
                && apt-get install -y --no-install-recommends \
                    curl \
                    fontconfig \
                    g++ \
                    libfreetype6 \
                    libicu-dev \
                    libjpeg-dev \
                    libpng-dev \
                    postgresql-server-dev-all \
                    bzip2 \
                    git \
                    postgresql-9.4 \
                    libpcre3-dev \
                && rm -rf /var/lib/apt/lists/* \

                && docker-php-ext-install exif \
                && docker-php-ext-configure gd --with-jpeg-dir=/usr/ \
                && docker-php-ext-install gd \
                && docker-php-ext-install intl \
                && docker-php-ext-install mbstring \
                && docker-php-ext-install pdo_pgsql \
                && docker-php-ext-install zip


####################
# Add config files #
####################
COPY            ./php.ini /usr/local/etc/php/php.ini
COPY            ./init-container.sh /root/init-container.sh


############
# Composer #
############
RUN             curl -sS https://getcomposer.org/installer | php \
                && mv composer.phar /usr/local/bin/composer \
                && mkdir -p /var/www/.composer/vendor/


#######
# Run #
#######
CMD             bash /root/init-container.sh /www && php-fpm
