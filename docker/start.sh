#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -d vendor ]; then
  composer install --no-dev --optimize-autoloader --no-interaction
fi

AUTO_DB_INSTALL="${AUTO_DB_INSTALL:-true}"

if [ "${AUTO_DB_INSTALL}" = "true" ]; then
  echo "Waiting for database connection..."
  i=0
  until php shell.php db:status >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "${i}" -ge 60 ]; then
      echo "Database is not reachable after 120 seconds."
      exit 1
    fi
    sleep 2
  done

  echo "Running database install..."
  php shell.php db:install
fi

exec apache2-foreground
