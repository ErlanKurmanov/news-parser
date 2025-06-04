# news-parser/Dockerfile.php

FROM php:8.1-fpm-alpine

# Install system dependencies required for PHP extensions
# build-base, autoconf, automake - for compilation
# libxml2-dev - for DOM/XML (needed for Symfony DomCrawler)
# curl-dev, openssl-dev - for Guzzle and HTTPS
# oniguruma-dev - for mbstring
# libzip-dev - for zip archives (often used by Composer)
# icu-dev - for internationalization (useful to have)
RUN apk add --no-cache \
$PHPIZE_DEPS \
curl-dev \
libxml2-dev \
libzip-dev \
oniguruma-dev \
icu-dev \
openssl-dev

# Install PHP extensions
# dom and xml - for working with XML/HTML
# curl - for HTTP requests (Guzzle)
# mbstring - for multi-byte strings
# zip - for working with zip archives
# intl - for internationalization
RUN docker-php-ext-install dom xml curl mbstring zip intl

# Install Composer globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory in the container
WORKDIR /var/www/html

# Copy composer.json and composer.lock to install dependencies
# This is done before copying the rest of the code to leverage Docker's cache.
# If these files don't change, dependencies won't be reinstalled on every build.
COPY composer.json composer.lock ./

# Install PHP dependencies via Composer
# --no-interaction: do not ask interactive questions
# --no-plugins: do not activate plugins (if not needed)
# --no-scripts: do not execute scripts from composer.json (if not needed during image build)
# --prefer-dist: prefer downloading distributions (archives) instead of cloning from VCS
# --optimize-autoloader: optimize the class autoloader for production
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist --optimize-autoloader

# Copy the rest of the application code into the working directory
COPY . .

# Change file ownership to www-data (the user PHP-FPM runs as)
# This is important for correct file operations if PHP needs to write anything (logs, cache)
RUN chown -R www-data:www-data /var/www/html

# PHP-FPM listens on port 9000 by default
EXPOSE 9000

# Command to start PHP-FPM
CMD ["php-fpm"]