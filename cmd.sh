#!/bin/bash

# set port
[[ -z "$2" ]] && port=8000 || port=$2

# func / start server
start() {
  php -S 0.0.0.0:$port server.php
}

# initial docker environment
init-docker() {
  mkdir data
  mkdir data/upload
  chmod 707 data
  chmod 707 data/upload
  curl -O https://raw.githubusercontent.com/redgoose-dev/goose-api/master/resource/.env.example
  curl -O https://raw.githubusercontent.com/redgoose-dev/goose-api/master/docker-compose.yml
  mv .env.example .env
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

  init-docker)
    init-docker
    ;;

  *)
    echo "Usage: ./dev-server.sh {start|ready|install|make-token|reset-password|init-docker}" >&2
    exit 3
    ;;
esac