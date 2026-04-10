FROM php:8.2-apache

# Instala extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Habilita mod_rewrite do Apache (necessário para o .htaccess funcionar)
RUN a2enmod rewrite

# Configura o Apache para permitir .htaccess na pasta do projeto
RUN echo '<Directory /var/www/html/sigeti>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/sigeti.conf \
    && a2enconf sigeti

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html/sigeti

# Copia os arquivos do projeto
COPY . .

# Instala as dependências PHP (se a pasta vendor não existir)
RUN if [ ! -d "vendor" ]; then composer install --no-interaction --optimize-autoloader; fi

# Permissões para o Apache
RUN chown -R www-data:www-data /var/www/html/sigeti \
    && chmod -R 755 /var/www/html/sigeti

EXPOSE 80
