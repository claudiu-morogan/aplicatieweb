FROM php:8.2-apache

# Install curl for healthcheck and basic utilities
RUN apt-get update \
 && apt-get install -y --no-install-recommends curl \
 && rm -rf /var/lib/apt/lists/*

# Copy project into web root (build context is project root)
COPY . /var/www/html/

# Ensure Apache user owns files
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Simple HTTP healthcheck
HEALTHCHECK --interval=10s --timeout=5s --start-period=5s --retries=3 \
  CMD curl -f http://localhost/ || exit 1
