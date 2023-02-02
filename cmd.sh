#!/bin/sh

# get .env
export $(grep -v '^#' .env | xargs)

# set port
[[ -z "$2" ]] && port=8000 || port=$2

# func / start server
start() {
  php -S 0.0.0.0:$port server.php
}

case "$1" in
  start)
    start
    ;;

  ready)
    php resource/tools.php ready
    ;;

  install)
    php resource/tools.php install
    ;;

  make-token)
    php resource/tools.php make-token
    ;;

  reset-password)
    php resource/tools.php reset-password
    ;;

  *)
    echo "Usage: ./cmd.sh {start|ready|install|make-token|reset-password}" >&2
    exit 3
    ;;
esac
