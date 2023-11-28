FROM php:8.1-cli

# Install PostgreSQL drivers
RUN apt-get update && apt-get install -y \
    libpq-dev \
    cron \
    && docker-php-ext-install pdo_pgsql pgsql pcntl

RUN touch /var/log/cron.log

# Copy the entrypoint script and set permissions
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Set the entrypoint script to be executed
ENTRYPOINT ["/entrypoint.sh"]
