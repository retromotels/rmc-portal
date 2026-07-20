#!/bin/bash
# RMC portal deploy script — run ON THE SERVER via cPanel Terminal.
#
#   ./deploy.sh staging     # pull latest and deploy to staging.retromotels.com
#   ./deploy.sh prod        # pull latest and deploy to portal.retromotels.com
#
# It pulls the repo, overlays the app files onto the live Laravel install
# (never touching vendor/, .env or storage/), then clears the caches.
set -euo pipefail

TARGET="${1:-}"
BRANCH="${BRANCH:-main}"
REPO="$HOME/repos/rmc-portal"

case "$TARGET" in
  prod)    APP="$HOME/public_html/portal.retromotels.com" ;;
  staging) APP="$HOME/public_html/staging.retromotels.com" ;;
  *) echo "usage: ./deploy.sh [staging|prod]"; exit 1 ;;
esac

if [ ! -d "$APP" ]; then echo "target app dir not found: $APP"; exit 1; fi

echo "==> Pulling $BRANCH into $REPO"
git -C "$REPO" fetch origin
git -C "$REPO" reset --hard "origin/$BRANCH"
REV="$(git -C "$REPO" rev-parse --short HEAD)"

echo "==> Overlaying app files onto $APP"
for d in app config database resources routes public; do
  if [ -d "$REPO/$d" ]; then
    cp -a "$REPO/$d/." "$APP/$d/"
  fi
done

echo "==> Clearing caches"
cd "$APP"
php artisan migrate --force || true
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "==> DEPLOYED $TARGET @ $REV  ($(date))"
