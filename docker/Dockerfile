# Используем официальный образ PHP с Apache
FROM php:8.2-apache

# Копируем файлы проекта в директорию веб-сервера контейнера
COPY ./src /var/www/html

# Включаем необходимые модули PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Настройки Apache (если нужно)
RUN a2enmod rewrite