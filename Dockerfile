FROM unit:1.33.0-php8.3

COPY ./unit-config.json /docker-entrypoint.d/unit-config.json

# PHP extension requirements
RUN apt-get update && apt-get install -y zip libzip-dev

WORKDIR /applink
COPY ./public ./public
COPY ./src ./src
COPY composer.json .
COPY composer.lock .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV PATH="$PATH:/usr/local/bin"
RUN docker-php-ext-configure zip && docker-php-ext-install zip
RUN composer install

EXPOSE 80
