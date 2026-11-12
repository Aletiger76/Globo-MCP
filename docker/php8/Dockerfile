# Globo MCP - container PHP 8.3 + Apache
# Identico al Dockerfile di Globo8/5stelle (stesso stack core), riprodotto qui per
# rendere il clone autosufficiente. In Fase 2 potra' essere ereditato dal core.

FROM php:8.3-apache

LABEL project="globo-mcp" \
      stage="clone-dev" \
      php-version="8.3"

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Europe/Rome

# --- Pacchetti di sistema necessari per le estensioni PHP ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libxml2-dev \
        libxslt1-dev \
        libcurl4-openssl-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        zlib1g-dev \
        libbz2-dev \
        unzip \
        git \
        vim-tiny \
        less \
        rsync \
    && rm -rf /var/lib/apt/lists/*

# --- Estensioni PHP ---
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        pdo \
        pdo_mysql \
        mbstring \
        xml \
        xsl \
        curl \
        zip \
        bz2 \
        intl \
        opcache \
        bcmath \
        sockets \
        exif \
        soap

# --- Configurazione PHP per sviluppo ---
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN { \
        echo "; --- Globo dev overrides ---"; \
        echo "display_errors = On"; \
        echo "display_startup_errors = On"; \
        echo "error_reporting = E_ALL"; \
        echo "log_errors = On"; \
        echo "error_log = /var/log/apache2/php_errors.log"; \
        echo "date.timezone = Europe/Rome"; \
        echo "memory_limit = 256M"; \
        echo "upload_max_filesize = 64M"; \
        echo "post_max_size = 64M"; \
        echo "max_execution_time = 120"; \
        echo "short_open_tag = On"; \
    } > /usr/local/etc/php/conf.d/zz-globo-dev.ini

# --- Configurazione Apache ---
RUN a2enmod rewrite headers expires

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# --- Permessi sulla webroot ---
RUN chown -R www-data:www-data /var/www/html

# --- Composizione webroot (core + custom + dev-overlay) all'avvio ---
# I tre alberi sorgente sono bind-mount read-only in /opt/{core,custom,dev-overlay};
# l'entrypoint li fonde in /var/www/html a ogni avvio. Vedi docker-compose.yml.
COPY docker/php8/compose-webroot.sh /usr/local/bin/compose-webroot.sh
RUN chmod +x /usr/local/bin/compose-webroot.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/compose-webroot.sh"]
