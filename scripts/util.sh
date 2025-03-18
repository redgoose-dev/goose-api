#!/bin/sh

# get .env
export $(grep -v '^#' .env | xargs)

# set working directory
cd "$(dirname "$0")/.."

case "$1" in

  install)
    uv run install.py
    ;;

  dev)
    uv run uvicorn main:app --reload --host 0.0.0.0 --port 8000
    ;;

  *)
    echo "Usage: ${script} {install|dev}" >&2
    exit 0
    ;;

esac
