#!/bin/sh
# Take a database backup at container start, then every 6 hours.
while true; do
  php /usr/local/bin/backup-db.php || true
  sleep 21600
done
