FROM php:8.4-cli

ARG DEBIAN_FRONTEND=noninteractive

# Instala dependencias del sistema: JRE para epubcheck, herramientas, y extensiones PHP necesarias
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        curl \
        default-jre-headless \
        libzip-dev \
        libxml2-dev \
        zip \
    && docker-php-ext-install zip dom \
    && rm -rf /var/lib/apt/lists/*

# Traer composer desde la imagen oficial de composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar solo composer files para aprovechar cache de docker
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

# Copiar el resto del proyecto
COPY . .

# Intentar instalar epubcheck por apt; si no está disponible descargamos release v5.3.0 y extraemos JAR como fallback
RUN set -eux; \
    if apt-get update && apt-get install -y --no-install-recommends epubcheck; then \
        echo "epubcheck_via_apt=true" > /tmp/epubcheck_source; \
    else \
        EPUB_VER=5.3.0; \
        ZIP=epubcheck-${EPUB_VER}.zip; \
        curl -fsSL -o /tmp/${ZIP} https://github.com/w3c/epubcheck/releases/download/v${EPUB_VER}/${ZIP}; \
        unzip -q /tmp/${ZIP} -d /opt/epubcheck; \
        JARPATH=$(find /opt/epubcheck -type f -name 'epubcheck*.jar' | head -n1); \
        if [ -n "$JARPATH" ]; then \
            mv "$JARPATH" /opt/epubcheck/epubcheck.jar || true; \
        fi; \
        echo "epubcheck_via_jar=true" > /tmp/epubcheck_source; \
    fi

# Default command: run the test suite (users can override)
CMD ["bash", "-lc", "composer install --no-interaction && vendor/bin/phpunit --colors=always"]
