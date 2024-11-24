FROM unit:1.33.0-php8.3

COPY ./unit-config.json /docker-entrypoint.d/unit-config.json

WORKDIR /applink
COPY ./public .
COPY ./src .
COPY composer.json .
COPY composer.lock .

EXPOSE 80
