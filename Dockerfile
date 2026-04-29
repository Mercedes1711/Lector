# Usar una imagen oficial de PHP con Apache
FROM php:8.2-apache

# Instalar dependencias del sistema y extensiones de PHP
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    openssh-server \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd

# Usar la configuración de producción y ajustar límites directamente creando un ini personalizado
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && \
    echo "upload_max_filesize = 128M" > /usr/local/etc/php/conf.d/99-custom.ini && \
    echo "post_max_size = 128M" >> /usr/local/etc/php/conf.d/99-custom.ini && \
    echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/99-custom.ini && \
    echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/99-custom.ini

# Forzar la carga de la configuración en Apache
RUN echo 'PHPIniDir "/usr/local/etc/php"' >> /etc/apache2/apache2.conf


# Configurar SSH
RUN mkdir -p /var/run/sshd && \
    echo 'root:root' | chpasswd && \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    sed -i 's/#PasswordAuthentication yes/PasswordAuthentication yes/' /etc/ssh/sshd_config


# Habilitar mod_rewrite y ssl de Apache
RUN a2enmod rewrite ssl socache_shmcb

# Generar un certificado SSL autofirmado
RUN mkdir -p /etc/apache2/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/server.key \
    -out /etc/apache2/ssl/server.crt \
    -subj "/C=ES/ST=Madrid/L=Madrid/O=Manga_verso/OU=IT/CN=localhost"

# Habilitar el sitio SSL por defecto y configurar las rutas de los certificados
RUN a2ensite default-ssl && \
    sed -i 's|/etc/ssl/certs/ssl-cert-snakeoil.pem|/etc/apache2/ssl/server.crt|g' /etc/apache2/sites-available/default-ssl.conf && \
    sed -i 's|/etc/ssl/private/ssl-cert-snakeoil.key|/etc/apache2/ssl/server.key|g' /etc/apache2/sites-available/default-ssl.conf

# Configurar Apache para permitir .htaccess (AllowOverride All)
RUN echo '<Directory "/var/www/html">\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Configurar el directorio de trabajo
WORKDIR /var/www/html

# Copiar el código del proyecto
COPY . /var/www/html/

# Copiar el script de entrypoint y darle permisos de ejecución
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Asegurar permisos correctos para el servidor web
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Ajustar permisos específicos para la carpeta de subidas si existe
RUN mkdir -p /var/www/html/Manga && chown -R www-data:www-data /var/www/html/Manga

# Exponer el puerto 80, 443 (HTTPS) y 22 (SSH)
EXPOSE 80 443 22

# Establecer el script de entrypoint
ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]

