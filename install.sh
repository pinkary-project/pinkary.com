#!/usr/bin/env bash

echo "### Installing composer dependencies"
composer install

echo "### Installing npm dependencies"
npm install

echo "### Setting up env"
cp .env.example .env
php artisan key:generate

echo "### Preparing database"
touch database/database.sqlite
php artisan migrate

echo "### Linking storage to public directory"
php artisan storage:link

echo "### Building assets"
npm run build

echo "### Almost ready! Here you go..."
php artisan serve
