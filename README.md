# goose-api

redgoose 컨텐츠 API 프로젝트
이 API 프로그램은 [Goose](https://github.com/redgoose-dev/goose)에서 시작된 개인용 CMS 중 하나의 프로젝트입니다.

## Usage

로컬 개발 환경에서 구동하기 위하여 다음과 같이 실행합니다.
패키지 매니저는 [UV](https://docs.astral.sh/uv/)가 필요합니다.

```shell
# clone repo
git clone https://github.com/redgoose-dev/goose-api.git
cd goose-api

# install uv (if not installed)
curl -LsSf https://astral.sh/uv/install.sh | sh

# activate virtualenv
uv sync

# install app
./scripts/util.sh install

# run server
uv run uvicorn main:app --reload --host 0.0.0.0 --port 8000
./scripts/util.sh dev # run shell script
```

서버 스크립트 실행은 [main.py](main.py)파일에서 시작합니다.

## Tech Stack

주요 테크스택은 다음과 같습니다.

- [Python 3](https://www.python.org)
- [UV](https://docs.astral.sh/uv/)
- [FastAPI](https://fastapi.tiangolo.com)
- [SQLite](https://sqlite.org)

자세한 구성은 [pyproject.toml](pyproject.toml)파일을 참고하세요.


## Docker

> docker hub: https://hub.docker.com/r/redgoose/goose-api

### docker-compose

```docker-compose.yml
services:
  goose-api:
    container_name: slideshow-local
    image: redgoose/goose-api:latest
    ports:
      - "8000:80"
    volumes:
      - ./data:/app/data
      - ./.env:/app/.env
      - ./.env.local:/app/.env.local
    environment:
      - PYTHONUNBUFFERED=1
```
