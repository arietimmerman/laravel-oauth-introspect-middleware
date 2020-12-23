FROM php:8.0-alpine

RUN apk add --no-cache git jq moreutils
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer create-project --prefer-dist laravel/laravel example && \
    cd example

WORKDIR /example

COPY . /laravel-oauth-introspect-middleware
RUN jq '.repositories=[{"type": "path","url": "/laravel-oauth-introspect-middleware"}]' ./composer.json | sponge ./composer.json

RUN composer require arietimmerman/laravel-oauth-introspect-middleware @dev

RUN touch ./.database.sqlite && \
    echo "DB_CONNECTION=sqlite" >> ./.env && \
    echo "DB_DATABASE=/example/.database.sqlite" >> ./.env && \
    echo "APP_URL=http://localhost:18123" >> ./.env

RUN php artisan migrate

CMD php artisan serve --host=0.0.0.0 --port=8000
