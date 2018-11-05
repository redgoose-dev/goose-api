#!/bin/bash

docker rm $(docker ps -a -q) -f
rm -rf ./data/db

docker run --name goose-api-db \
  -e MYSQL_ROOT_PASSWORD=1234 \
  -e MYSQL_DATABASE=goose \
  -e MYSQL_USER=goose \
  -e MYSQL_PASSWORD=1234 \
  -p 3306:3306 \
  -v `pwd`/data/db:/var/lib/mysql/ \
  -d mariadb

sleep 3

ls -al ./data/db

docker run --name goose-api \
  -v `pwd`/resource/nginx.conf:/etc/nginx/nginx.conf \
  -v `pwd`/.env:/goose/.env \
  -v `pwd`/data/:/goose/data/ \
  -v `pwd`/data/log-nginx:/var/log/nginx \
  -p 8000:80 \
  --link goose-api-db:mysql \
  -d goose-api

sleep 10

docker exec goose-api ./script.sh install