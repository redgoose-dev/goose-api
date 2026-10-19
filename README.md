# goose-api

[Goose](https://github.com/redgoose-dev/goose)에서 시작된 개인용 CMS의 콘텐츠 API입니다.
Bun과 Elysia로 서버를 실행하며, SQLite에 데이터를 저장합니다.

## 기술 스택

| 기술 | 용도 |
| --- | --- |
| TypeScript | 서버 및 유틸리티 코드 작성 |
| Bun | 실행 환경, 패키지 관리, 번들 빌드 |
| Elysia | HTTP 라우팅과 요청 스키마 검증 |
| SQLite (`bun:sqlite`) | 콘텐츠와 로그 저장 |
| Sharp | 이미지 변환 및 리사이즈 |
| Logixlysia | 요청 로깅과 로그 transport 연동 |
| JSON Web Token / bcryptjs | 인증 토큰과 비밀번호 해시 처리 |

의존성과 실행 스크립트는 [package.json](package.json)에서 확인할 수 있습니다.

## 시작하기

Bun이 설치된 환경에서 다음 명령으로 프로젝트를 준비합니다.

```shell
git clone https://github.com/redgoose-dev/goose-api.git
cd goose-api
bun install
```

저장소의 `.env`를 기본 설정으로 사용합니다. 로컬 환경에서 변경할 값은 루트의 `.env.local`에 작성합니다.

```dotenv
SERVICE_NAME="GOOSE-API-DEV"
DEBUG="true"
HOST="0.0.0.0"
PORT="3000"
PATH_DATA="./data"
PATH_URL="http://localhost:3000"
LOG_PRINT="true"
```

처음 실행할 때 데이터베이스와 초기 계정을 생성한 뒤 서버를 시작합니다.
설치 명령은 안내에 따라 계정 ID, 이름, 비밀번호를 입력하는 방식입니다.
기존 데이터가 있는 환경에서는 재설치할 필요가 없습니다.

```shell
bun run dev:util install
bun run dev:preview
```

위 설정에서는 `http://localhost:3000`으로 접속합니다. 서버 진입점은 [src/main.ts](src/main.ts)입니다.

## 빌드 및 실행

```shell
bun run build
bun run prod:preview
```

빌드 결과는 `dist/`에 생성됩니다. 빌드 후 유틸리티 명령은 `bun run prod:util [METHOD] [OPTIONS]`로 실행합니다.
`dev:preview`와 `prod:preview`는 각각 `NODE_ENV`를 설정하며, 서비스의 개발 모드와 디버그 출력은 `DEBUG`로 제어합니다.
운영 환경에서는 `DEBUG=false`로 설정하고, `.env`의 `AUTH_PASSWORD_ACCESS_SECRET`과 `AUTH_PASSWORD_REFRESH_SECRET`을 별도의 비밀 값으로 변경하세요.

## 주요 설정

| 환경변수 | 용도 |
| --- | --- |
| `HOST`, `PORT` | 서버의 수신 주소와 포트 |
| `PATH_DATA` | 데이터베이스, 업로드 파일, 캐시, 설정을 저장할 경로 |
| `PATH_URL` | 외부에서 접근할 API 기본 URL |
| `DEBUG` | 개발 모드 및 디버그 출력 |
| `LOG_PRINT` | 콘솔 로그 출력 여부 |
| `LOG_RECORD` | SQLite 로그 기록 여부 |
| `LOG_RECORD_RETENTION_DAYS` | SQLite 로그 보관 기간(일) |

전체 환경변수는 [.env](.env), 로그 설정은 [로깅 문서](src/libs/logging/README.md)를 참고하세요.

## CLI 사용법

프로젝트 루트에서 `bun run dev:util [METHOD] [OPTIONS]` 형식으로 실행합니다.
빌드 후에는 `dev:util`을 `prod:util`로 바꿔 실행합니다.

### 설치

프로그램에 필요한 데이터와 데이터베이스, 초기 계정을 만듭니다.
기존 데이터가 있으면 대화형 설치는 삭제 여부를 확인합니다. 계정 옵션을 모두 지정하면 기존 `PATH_DATA`의 내용을 확인 없이 삭제하고 다시 설치하므로 최초 설치에만 사용하세요.

```shell
# 진행에 대한 확인을 하며 초기 계정 정보를 입력해야 합니다.
bun run dev:util install

# 최초 설치를 자동으로 진행합니다.
bun run dev:util install --id goose --name GOOSE --password '<초기 비밀번호>'
```

### 삭제

`PATH_DATA` 디렉터리와 그 안의 데이터베이스, 업로드 파일, 캐시, 설정을 모두 삭제합니다.

```shell
# 진행에 대한 확인을 합니다.
bun run dev:util uninstall

# 확인없이 바로 언인스톨을 진행합니다.
bun run dev:util uninstall -y
```

### 비밀번호 재설정

비밀번호 프로바이더에서 사용하는 비밀번호를 재설정합니다.

```shell
bun run dev:util reset-password
```

### 캐시 정리

변환된 이미지 캐시와 이전 형식의 쿼리별 JSON 캐시를 정리합니다. 코드별 기본 메타데이터 JSON은 삭제하지 않습니다.

```shell
# 삭제 대상만 확인
bun run dev:util clean-cache --days 30 --dry-run

# 실제 삭제
bun run dev:util clean-cache --days 30 --execute
```

`--days`는 보관 기간이며 기본값은 30일입니다. 0보다 큰 숫자로 지정합니다.
`--execute`가 없으면 삭제하지 않고 대상만 확인합니다. `--dry-run`은 확인 모드를 명시하기 위한 옵션이며, 실제 동작은 `--execute` 유무로 결정되므로 두 옵션을 함께 사용하지 마세요.
캐시 정리는 서버 시작 시 자동으로 실행되지 않습니다.

## Docker

저장소의 `Dockerfile`로 이미지를 빌드하고 데이터 디렉터리를 연결해 실행할 수 있습니다.

```shell
docker build -t goose-api .
docker run --rm --name goose-api -p 3000:80 \
  -v "$(pwd)/data:/app/data" \
  goose-api
```

컨테이너는 처음 실행할 때 데이터가 없으면 초기 계정(`goose` / `1234`)을 생성합니다.
외부에 공개하기 전에 비밀번호를 변경하고 인증용 비밀 값을 설정하세요.
실행 중인 컨테이너의 비밀번호는 다음 명령으로 변경합니다.

```shell
docker exec -it goose-api bun run prod:util reset-password
```

환경변수는 `docker run`의 `-e` 또는 `--env-file`로 전달할 수 있습니다.
Docker Compose 설정 파일은 저장소에 포함되어 있지 않습니다.
실행 중인 컨테이너에서 캐시를 정리할 때는 다음 명령을 사용합니다.

```shell
# 삭제 대상 확인
docker exec goose-api bun run prod:util clean-cache --days 30 --dry-run

# 실제 삭제
docker exec goose-api bun run prod:util clean-cache --days 30 --execute
```

## 문서

- [소스 구조](src/README.md): 진입점, 디렉터리 구성, 요청 처리 흐름
- [라우트 문서](src/routes/README.md): 라우트 구성 및 핸들러 작성 안내
- [라이브러리 문서](src/libs/README.md)
- [로깅 문서](src/libs/logging/README.md)
