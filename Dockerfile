# syntax=docker/dockerfile:1

# Production dependencies only, with an authoritative classmap autoloader
FROM composer:2.10.3 AS vendor

WORKDIR /applink
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-autoloader --no-scripts \
        --ignore-platform-reqs
COPY src ./src
RUN composer dump-autoload --no-dev --classmap-authoritative --ignore-platform-reqs


FROM dunglas/frankenphp:1.13.2-php8.5.12-alpine

LABEL org.opencontainers.image.title="applink" \
      org.opencontainers.image.description="One shareable link to all your app market listings" \
      org.opencontainers.image.source="https://github.com/smartcompanion-app/applink" \
      org.opencontainers.image.licenses="BSD-2-Clause"

ENV APPLINK_STORAGE=/applink/storage

# Run as an unprivileged user, which still may bind port 80
RUN apk add --no-cache --virtual .build-deps libcap \
    && adduser -D -H -u 1000 applink \
    && setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
    && chown -R applink:applink /config/caddy /data/caddy \
    && apk del .build-deps \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-applink.ini"
COPY docker/Caddyfile /etc/frankenphp/Caddyfile

WORKDIR /applink
COPY --from=vendor /applink/vendor ./vendor
COPY public ./public
COPY src ./src
RUN mkdir -p storage/configuration storage/icons

USER applink
EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1/healthz || exit 1

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
