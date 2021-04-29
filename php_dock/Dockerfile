FROM php:7.4.16-apache-buster

RUN apt-get update && apt-get install -y curl bash sendmail
RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80
EXPOSE 443