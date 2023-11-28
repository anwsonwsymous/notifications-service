#!/bin/bash

# Copy php.ini
cp /app/docker/php.ini /usr/local/etc/php/php.ini

# Copy the crontab file to the cron.d directory
cp /app/docker/crontab /etc/cron.d/notifications_crontab

# Give execution rights on the cron job
chmod 0644 /etc/cron.d/notifications_crontab

# Apply cron job
crontab /etc/cron.d/notifications_crontab

# Start cron in background
cron

# Tail the cron log in the foreground
tail -f /var/log/cron.log

