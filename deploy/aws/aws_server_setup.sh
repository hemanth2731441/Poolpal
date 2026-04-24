#!/usr/bin/env bash
set -euo pipefail

DOMAIN="${1:-poolpal.in}"
WWW_DOMAIN="www.${DOMAIN}"

sudo apt update
sudo apt install -y apache2 unzip certbot python3-certbot-apache

sudo a2enmod rewrite headers
sudo systemctl restart apache2

# Ensure Apache honors .htaccess for /var/www/html
sudo sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf
sudo systemctl restart apache2

# Basic firewall rules
sudo ufw allow OpenSSH || true
sudo ufw allow 'Apache Full' || true
sudo ufw --force enable || true

# Prepare deployment directories
sudo mkdir -p /var/www/releases
sudo chown -R "$USER":"$USER" /var/www/releases

echo "Server bootstrap complete. Next: upload release zip to /var/www/releases" 

echo "After DNS points to this server, run SSL command:"
echo "sudo certbot --apache -d ${DOMAIN} -d ${WWW_DOMAIN}"
