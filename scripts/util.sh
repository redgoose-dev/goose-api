#!/bin/sh

# get .env
export $(grep -v '^#' .env | xargs)

source .venv/bin/activate

case "$1" in

  install)
    pip install "$2"
    ;;

  setup)
    python src/setup.py
    ;;

  *)
    echo "Usage: ${script} {install|setup}" >&2
    exit 0
    ;;

esac

deactivate
