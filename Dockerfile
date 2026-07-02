# Usamos una imagen oficial de PHP.
FROM php:8.4-cli

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring intl pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer el directorio de trabajo dentro del contenedor
WORKDIR /var/www/html

# Copiar el código del backend (directorio home) al contenedor
COPY home/ .

# Instalar dependencias de Composer para producción
RUN composer install --no-dev --optimize-autoloader

# Exponer el puerto por defecto (Render inyectará el puerto real en $PORT)
EXPOSE 8000

# Comando para iniciar Laravel en Render usando el puerto dinámico de Render
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
