# goose-api

이것은 개인용 CMS [Goose](https://github.com/RedgooseDev/goose) 프로젝트에서 API 기능에 집중한 프로젝트입니다.  
[goose manager](https://github.com/redgoose-dev/goose-manager) 에서 컨텐츠를 제작하고 API를 활용하여 다른 플랫폼에서 사용하기 위한 용도로 만들어진 마이크로 CMS API라고 볼 수 있습니다.


## 함께 사용할 수 있는 프로그램

- goose-manager: https://github.com/redgoose-dev/goose-manager
- goose-app: https://github.com/redgoose-dev/goose-app


## 사용을 위한 환경

다음과 같은 환경을 구성하기 위하여 필요한 패키지들을 설치할 필요가 있습니다.

- apache or nginx
- php 5.6 or higher version
- mysql
- composer


## Documentation

goose-api 설치에 관란 자세한 내용은 wiki에서 확인할 수 있습니다.

https://github.com/redgoose-dev/goose-api/wiki


## TODO

- [ ] nginx.conf 설정 만들기 (포트 80을 목표)
- [ ] 빌드 스크립트 이후에 해야할일들 정하기 (readme에 작성 필요있음.)
- [ ] docker에서 .env 수정하는 명령 찾아보기
- [ ] docker에서 `./script.sh install` 명령 실행할 수 있도록 스크립트 작성하기
- [ ] mysql과의 접속에 대하여 검증해보기