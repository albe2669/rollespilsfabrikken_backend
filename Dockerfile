FROM php:8.3-cli

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    default-mysql-client \
    libfreetype-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    libpng-dev \
    && docker-php-source extract \
	&& docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install zip pdo pdo_mysql gd \
	&& docker-php-source delete

COPY . /var/www/html

WORKDIR /var/www/html

RUN composer install

CMD ["php", "artisan", "serve"]
