FROM nginx:stable-alpine

# Remove default nginx configs
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom nginx config
COPY default.conf /etc/nginx/conf.d/default.conf

# Copy public assets (optional, recommended for production)
# COPY ../../src/public /var/www/html/public

WORKDIR /var/www/html
