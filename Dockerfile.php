FROM php:8.3-fpm

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
  git \
  unzip \
  libpng-dev \
  libjpeg-dev \
  libfreetype6-dev \
  libzip-dev \
  libxml2-dev \
  libxslt-dev \
  libicu-dev \
  libonig-dev \
  libpq-dev \
  libmagickwand-dev \
  pkg-config \
  libmariadb-dev \
  curl \
  brotli \
  docker.io \
&& apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
  gd \
  zip \
  intl \
  pdo \
  pdo_mysql \
  opcache \
  exif \
  pcntl \
  bcmath


# 3. Install PECL extensions (Redis & Imagick)
RUN pecl install redis imagick \
  && docker-php-ext-enable redis imagick

# 4. Install kubectl
RUN curl -LO "https://dl.k8s.io/release/$(curl -L -s https://dl.k8s.io/release/stable.txt)/bin/linux/amd64/kubectl" \
  && mv kubectl /usr/local/bin/ && chmod +x /usr/local/bin/kubectl

# 5. Install Composer (из официального образа)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Install Node.js & Yarn
RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
  && apt-get install -y nodejs \
  && npm install -g yarn


# 7. Configure PHP & Logging
RUN mkdir -p /var/www/html/logs && chown -R www-data:www-data /var/www/html/logs \
  && echo "log_errors = On" >> /usr/local/etc/php/conf.d/dc-logging.ini \
  && echo "error_log = /var/www/html/logs/php_errors.log" >> /usr/local/etc/php/conf.d/dc-logging.ini \
  && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/dc-logging.ini \
  && echo "display_errors = Off" >> /usr/local/etc/php/conf.d/dc-logging.ini

# 8. Drush (Global)
RUN composer global require drush/drush
ENV PATH="/root/.composer/vendor/bin:${PATH}"

# 9. Working Directory
WORKDIR /var/www/html/web/

# 10. Entrypoint & Permissions
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# PHP
COPY ./data/90-custom.ini /usr/local/etc/php/conf.d/90-custom.ini

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm", "-F"]

EXPOSE 9000
