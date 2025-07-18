# Utilise l'image officielle PHP avec Apache
FROM php:8.2-apache

# Copie tout le code dans le dossier web de l'image
COPY . /var/www/html/

# Active mod_rewrite (utile pour les routes propres)
RUN a2enmod rewrite

# Donne les bons droits
RUN chown -R www-data:www-data /var/www/html

# Expose le port 80 (par défaut pour Apache)
EXPOSE 80