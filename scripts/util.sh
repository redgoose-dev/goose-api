#!/bin/sh

# get .env
export $(grep -v '^#' .env | xargs)


case "$1" in

  install)
    pip install "$2"
    pip freeze > requirements.txt
    ;;

  *)
    echo "Usage: ${script} {install}" >&2
    exit 0
    ;;

esac
