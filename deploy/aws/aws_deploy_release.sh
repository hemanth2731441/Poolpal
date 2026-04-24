#!/usr/bin/env bash
set -euo pipefail

RELEASE_ZIP="${1:-/var/www/releases/poolpal-aws-release.zip}"
WEB_ROOT="/var/www/html"
BACKUP_ROOT="/var/www/backups"
STAMP="$(date +%Y%m%d-%H%M%S)"

if [[ ! -f "$RELEASE_ZIP" ]]; then
  echo "Release zip not found: $RELEASE_ZIP"
  exit 1
fi

sudo mkdir -p "$BACKUP_ROOT"
if [[ -d "$WEB_ROOT" ]]; then
  sudo tar -czf "$BACKUP_ROOT/webroot-$STAMP.tar.gz" -C "$WEB_ROOT" . || true
fi

TMP_DIR="/tmp/poolpal-release-$STAMP"
mkdir -p "$TMP_DIR"
unzip -oq "$RELEASE_ZIP" -d "$TMP_DIR"

# Clean web root except hidden server files
sudo find "$WEB_ROOT" -mindepth 1 -maxdepth 1 ! -name '.well-known' -exec rm -rf {} +

# Copy release into web root
sudo cp -a "$TMP_DIR"/. "$WEB_ROOT"/

# Set permissions
sudo chown -R www-data:www-data "$WEB_ROOT"
sudo find "$WEB_ROOT" -type d -exec chmod 755 {} +
sudo find "$WEB_ROOT" -type f -exec chmod 644 {} +

# Keep deploy scripts executable if shipped accidentally
sudo chmod +x "$WEB_ROOT"/*.sh 2>/dev/null || true

rm -rf "$TMP_DIR"

sudo apache2ctl configtest
sudo systemctl reload apache2

echo "Deployment complete."
echo "Backup stored at: $BACKUP_ROOT/webroot-$STAMP.tar.gz"
