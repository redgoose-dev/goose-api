# goose-api

redgoose 컨텐츠 API 프로젝트
이 API 프로그램은 [Goose](https://github.com/redgoose-dev/goose)에서 시작된 개인용 CMS 중 하나의 프로젝트입니다.

## Usage

로컬 개발 환경에서 구동하기 위하여 다음과 같이 실행합니다.

```shell
# clone repo
git clone https://github.com/redgoose-dev/goose-api.git
cd goose-api

# create virtualenv
python -m venv .venv

# activate virtualenv
source .venv/bin/activate

# install dependencies
pip install -r requirements.txt

# install app
./scripts/util.sh install

# run server
unicorn main:app --reload --host 0.0.0.0 --port 8000

# deactivate virtualenv
deactivate
```

서버 스크립트 실행은 `/main.py`파일에서 시작합니다.

## Tech Stack

TODO: 프로젝트에서 사용된 기술 스택입니다.

## Docker

TODO: 도커 환경에서 구동하기 가이드
