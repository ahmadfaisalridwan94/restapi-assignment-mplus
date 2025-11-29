FROM nginx:stable-alpine

RUN rm /etc/nginx/conf.d/default.conf

COPY docker/development/nginx/default.conf /etc/nginx/conf.d/default.conf

WORKDIR /var/www/html