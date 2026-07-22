#!/bin/sh

set -eu

FILE_DB="./data/db.sqlite"
FILE_PREFERENCE="./data/preference.json"

# check install and run app:install if not installed
if [ ! -f "$FILE_DB" ] || [ ! -f "$FILE_PREFERENCE" ]; then
  echo "[entrypoint] install required: missing $FILE_DB or $FILE_PREFERENCE"
  # clean partial install state if one of the install files already exists
  if [ -f "$FILE_DB" ] || [ -f "$FILE_PREFERENCE" ]; then
    bun run dev:util uninstall -y
  fi
  # install with default user
  bun run prod:util install \
    --id="goose" \
    --name="GoOSe" \
    --email="scripter@me.com" \
    --password="1234"
fi

exec "$@"
