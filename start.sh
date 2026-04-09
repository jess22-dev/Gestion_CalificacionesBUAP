#!/bin/bash

php artisan config:clear
php artisan cache:clear
php artisan view:clear

php artisan migrate --force

apache2-foreground