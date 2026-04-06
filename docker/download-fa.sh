#!/bin/sh
# Télécharge Font Awesome en local
FA_VERSION="6.5.0"
FA_URL="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/${FA_VERSION}/css/all.min.css"
FA_WEBFONTS="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/${FA_VERSION}/webfonts"

mkdir -p /var/www/html/public/fonts/fa/css
mkdir -p /var/www/html/public/fonts/fa/webfonts

curl -o /var/www/html/public/fonts/fa/css/all.min.css "$FA_URL"

for font in fa-solid-900.woff2 fa-regular-400.woff2 fa-brands-400.woff2; do
    curl -o "/var/www/html/public/fonts/fa/webfonts/$font" "$FA_WEBFONTS/$font"
done

# Corriger les chemins dans le CSS
sed -i 's|../webfonts/|/fonts/fa/webfonts/|g' /var/www/html/public/fonts/fa/css/all.min.css
