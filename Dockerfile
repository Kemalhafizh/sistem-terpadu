FROM php:8.4-fpm AS base

# Arguments defined in docker-compose.yml
ARG user
ARG uid

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (both MySQL and SQLite for flexibility)
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip opcache

# Configure OPcache for production performance
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable_cli=1'; \
    } > /usr/local/etc/php/conf.d/opcache-recommended.ini

# PHP production configuration
RUN { \
    echo 'upload_max_filesize=50M'; \
    echo 'post_max_size=50M'; \
    echo 'memory_limit=256M'; \
    echo 'max_execution_time=300'; \
    echo 'expose_php=Off'; \
    } > /usr/local/etc/php/conf.d/app-production.ini

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# Set working directory
WORKDIR /var/www

# --- Production build stage ---
FROM base AS production

# Copy application files
COPY --chown=$user:$user . /var/www

# Install Composer dependencies (no dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Install Node.js for Vite build
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && npm ci --ignore-scripts \
    && npm run build \
    && apt-get purge -y nodejs \
    && apt-get autoremove -y \
    && rm -rf node_modules \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Cache Laravel configuration
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Docker healthcheck
HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
    CMD php-fpm-healthcheck || exit 1

USER $user

# --- Development stage (default) ---
FROM base AS development

USER $user
