#!/bin/sh

# install
python install.py -y

# run server
uvicorn main:app --host 0.0.0.0 --port ${PORT:-80}
