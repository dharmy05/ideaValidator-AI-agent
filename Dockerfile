# -------------------------------------------------------
# 🧱 Stage 1: Build dependencies with Composer
# -------------------------------------------------------
FROM composer:2 AS build

WORKDIR /app

# Copy only composer files first (better caching)
COPY composer.json composer.lock ./

# Install dependencies (no dev for production)
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader

# Copy the rest of the app
COPY . .

# -------------------------------------------------------
# 🚀 Stage 2: Production image with PHP + Apache
# -------------------------------------------------------
FROM php:8.3-apache

# Install required PHP extensions
RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev libonig
