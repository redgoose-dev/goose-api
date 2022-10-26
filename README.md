# goose-api

이것은 개인용 CMS [Goose](https://github.com/redgoose-dev/goose) 프로젝트에서 API 기능에 집중한 프로젝트입니다.  
[goose manager](https://github.com/redgoose-dev/goose-manager) 에서 컨텐츠를 제작하고 API를 활용하여 다른 플랫폼에서 사용하기 위한 용도로 만들어진 `Micro CMS API`라고 볼 수 있습니다.

## 함께 사용할 수 있는 프로그램

- goose-manager: https://github.com/redgoose-dev/goose-manager
- goose-app: https://github.com/redgoose-dev/goose-app

## 사용을 위한 환경

다음과 같은 환경을 구성하기 위하여 필요한 패키지들을 설치할 필요가 있습니다.

- apache or nginx
- php 8.x or higher version
- mysql
- composer

## Documentation

goose-api 설치에 관란 자세한 내용은 [wiki](https://github.com/redgoose-dev/goose-api/wiki)에서 확인할 수 있습니다.

## docker

### build

```shell
# mac / intel
docker build -t redgoose/goose-api:latest .
# mac / m1 / linux
docker buildx build --platform=linux/amd64 -t redgoose/goose-api:latest .
# mac / m1 / local
docker buildx build --platform=linux/arm64/v8 -t redgoose/goose-api:latest .
```

### docker-composer

다음 코드를 참고해주세요.

```shell
version: "3.9"

services:
  goose:
    image: redgoose/goose-api:latest
    container_name: goose
    restart: unless-stopped
    volumes:
      - ./config/nginx.conf:/etc/nginx/nginx.conf
      - ./config/fpm-pool.conf:/etc/php81/php-fpm.d/www.conf
      - ./config/php.ini:/etc/php81/conf.d/custom.ini
      - ./config/supervisord.conf:/etc/supervisord.conf
      - ./.env:/app/.env
      - ./data:/app/data
    environment:
      - TZ=Asia/Seoul
```
