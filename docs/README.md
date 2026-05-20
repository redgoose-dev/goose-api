## Legacy Python API 분석 문서

이 문서는 `__LEGACY__/` 경로에 있는 이전 Python API 프로젝트를 빠르게 이해하기 위한 요약 문서입니다.
현재 저장소의 TypeScript 기반 구현과는 별개로, 과거 FastAPI + SQLite 기반 API 서비스의 구조와 동작 기준점을 정리합니다.

---

## 1. 프로젝트 개요

- 프로젝트 위치: `__LEGACY__/`
- 성격: 개인 CMS용 API 서버
- 주요 스택:
  - Python 3.13+
  - FastAPI
  - Uvicorn
  - SQLite
  - Pydantic v2
  - Loguru

관련 파일:

- `__LEGACY__/README.md`
- `__LEGACY__/pyproject.toml`
- `__LEGACY__/main.py`
- `__LEGACY__/src/api.py`

---

## 2. 실행 진입점

### 앱 엔트리

- `__LEGACY__/main.py`

내용상 `src.api` 에서 생성한 FastAPI 인스턴스를 그대로 `app` 으로 노출합니다.

```python
from src.api import api

app = api
```

즉, 실제 애플리케이션 조립은 `__LEGACY__/src/api.py` 에서 수행됩니다.

---

## 3. 개발 실행 방식

`__LEGACY__/README.md` 기준 로컬 개발 절차는 다음과 같습니다.

```shell
uv sync
uv run install.py
uv run uvicorn main:app --reload --host 0.0.0.0 --port 8000
```

### 의미

1. `uv sync`
   - Python 의존성 설치
2. `uv run install.py`
   - `data/` 디렉터리 및 SQLite DB 초기화
3. `uv run uvicorn main:app ...`
   - FastAPI 서버 실행

---

## 4. 환경 변수 로딩 방식

환경 변수 로딩은 `__LEGACY__/src/__init__.py` 와 `__LEGACY__/src/libs/util.py` 에서 수행됩니다.

### 동작 순서

- `setup_env()` 실행
- `.env` 로드
- `.env.local` 로드 (`override=True`)

### 주요 전역 값

`__LEGACY__/src/__init__.py` 에서 다음 전역 상수를 만듭니다.

- `__NAME__` → `SERVICE_NAME`
- `__VERSION__` → `pyproject.toml` 의 `project.version`
- `__DEV__`
- `__DEBUG__`
- `__USE_LOG__`
- `__RECORD_LOG__`
- `__PRINT_LOG__`

즉, 런타임 동작 일부는 `.env`, `.env.local`, `pyproject.toml` 조합으로 결정됩니다.

---

## 5. FastAPI 앱 구조

핵심 파일은 `__LEGACY__/src/api.py` 입니다.

### 포함된 주요 기능

- FastAPI 앱 생성
- 로거 초기화
- HTTP 미들웨어 등록
- 홈 엔드포인트 등록
- 전체 경로용 `OPTIONS` 프리플라이트 처리
- 기능별 라우터 연결
- 예외 핸들러 등록

### 미들웨어

HTTP 미들웨어는 다음 역할을 합니다.

- 요청 시작 시각 기록: `req.state.start_time`
- 디버그 모드에서 요청 시작/종료 로그 출력
- 이후 공통 응답 생성 시 `X-Process-Time` 헤더 계산에 사용

---

## 6. 라우터 구성

`__LEGACY__/src/api.py` 에서 다음 라우터를 연결합니다.

- `/` → 홈
- `/{path}` → `OPTIONS` 프리플라이트
- `/app`
- `/article`
- `/category`
- `/checklist`
- `/comment`
- `/file`
- `/json`
- `/nest`
- `/tag`
- `/auth`
- `/preference`
- `/mix`

실제 구현 위치는 `__LEGACY__/src/endpoints/` 하위입니다.

디렉터리 기준:

- `__LEGACY__/src/endpoints/app/`
- `__LEGACY__/src/endpoints/article/`
- `__LEGACY__/src/endpoints/auth/`
- `__LEGACY__/src/endpoints/category/`
- `__LEGACY__/src/endpoints/checklist/`
- `__LEGACY__/src/endpoints/comment/`
- `__LEGACY__/src/endpoints/file/`
- `__LEGACY__/src/endpoints/json/`
- `__LEGACY__/src/endpoints/mix/`
- `__LEGACY__/src/endpoints/nest/`
- `__LEGACY__/src/endpoints/preference/`
- `__LEGACY__/src/endpoints/tag/`

---

## 7. 홈 엔드포인트

홈 엔드포인트 구현은 `__LEGACY__/src/endpoints/get_home.py` 에 있습니다.

### 응답 데이터

- `message`
- `version`
- `dev`

메시지는 대략 다음 형태입니다.

- `Hello! {SERVICE_NAME}`

즉, 서비스 이름은 환경 변수 `SERVICE_NAME` 의 영향을 받습니다.

---

## 8. 공통 응답 처리 방식

공통 응답 유틸은 `__LEGACY__/src/output.py` 에 집중되어 있습니다.

### 주요 함수

- `text()`
- `success()`
- `buffer()`
- `redirect()`
- `empty()`
- `error()`
- `exc()`

### 특징

- CORS 성격의 헤더를 직접 조합해서 응답에 추가
- 요청 처리 시간(`X-Process-Time`) 헤더 지원
- 성공/에러 시 로깅 연동
- 에러 응답에 `Error-Code` 헤더 부여 가능

### 기본 헤더

코드 기준 기본 헤더는 다음 값을 포함합니다.

- `Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH`
- `Access-Control-Allow-Headers: Origin, Content-Type, Authorization, Accept`
- `Access-Control-Allow-Credentials: true`
- `Access-Control-Allow-Origin: *`

즉, FastAPI/Starlette의 표준 CORS 미들웨어 대신 프로젝트 자체 응답 유틸에서 헤더를 강하게 제어하는 방식입니다.

---

## 9. 예외 처리 방식

`__LEGACY__/src/api.py` 에는 세 가지 예외 핸들러가 등록되어 있습니다.

### 1) `StarletteHTTPException`

- 405를 사실상 404처럼 변환하는 특수 처리 존재
- 반환은 `output.error()` 사용

### 2) `RequestValidationError`

- 상태 코드 400
- `method`, `path`, `stack` 정보를 함께 반환

### 3) 일반 `Exception`

- 상태 코드 500
- `method`, `path`, `error` 정보를 함께 반환

이 구조 때문에, 레거시 API는 대부분의 실패를 프로젝트 전용 응답 포맷으로 감싸서 반환하는 성향이 있습니다.

---

## 10. 로깅 구조

로깅은 `__LEGACY__/src/modules/logger.py` 에 구현되어 있습니다.

### 사용 라이브러리

- `loguru`

### 환경 플래그

- `__USE_LOG__`
- `__RECORD_LOG__`
- `__PRINT_LOG__`

### 동작 방식

- 성공 로그와 에러 로그를 분리
- 파일 기록 가능
- 표준 에러 출력 가능
- 명령행용 `INFO` 로그도 별도 포맷 사용

### 파일 저장 위치

코드상 로그는 `success/`, `error/` 하위 날짜별 파일로 기록되도록 설계되어 있습니다.

---

## 11. 설치 스크립트 분석

설치 스크립트는 `__LEGACY__/install.py` 입니다.

### 역할

- 설치 여부 검사
- 리소스 디렉터리 생성
- 초기 설정 파일 복사
- SQLite DB 파일 생성 및 seed SQL 적용
- 손상된 리소스 재설치

### 주로 다루는 경로

- `data/`
- `data/upload/`
- `data/upload/origin/`
- `data/upload/cover/`
- `data/cache/`
- `data/log/`
- `data/db.sqlite`
- `data/preference.json`
- `resource/seed.sql`
- `resource/preference.json`

### 설치 로직 요약

- `data/` 가 없으면 신규 설치
- 일부 파일/디렉터리가 누락되면 손상 상태로 판단
- 이미 설치되어 있으면 재설치 여부를 묻는 구조
- `-y` 인자를 주면 일부 확인 과정을 자동화

---

## 12. 데이터 저장소

레거시 프로젝트는 SQLite 기반입니다.

- DB 파일: `__LEGACY__/data/db.sqlite`
- 초기 스키마/데이터: `__LEGACY__/resource/seed.sql`

즉, 서버를 처음 사용할 때 별도 외부 DB 서버 없이 로컬 파일 DB로 동작합니다.

---

## 13. 테스트 구조

테스트는 `__LEGACY__/tests/` 아래에 있습니다.

예시 파일:

- `home.py`
- `app.py`
- `article.py`
- `auth.py`
- `category.py`
- `checklist.py`
- `comment.py`
- `file.py`
- `json.py`
- `mix.py`
- `nest.py`

### 테스트 특징

- `fastapi.testclient.TestClient` 사용
- `main.py` 의 `app` 객체를 직접 로드
- 일부 인증 테스트는 `TEST_ACCESS_TOKEN` 환경 변수에 의존

### 공통 테스트 설정

- `__LEGACY__/tests/__init__.py`
  - `setup_env()` 실행
  - 기본 Authorization 헤더 구성
- `__LEGACY__/tests/conftest.py`
  - 커스텀 pytest 옵션 추가

### pytest 설정

`__LEGACY__/pytest.ini`

- `asyncio_mode = auto`
- `asyncio_default_fixture_loop_scope = function`
- `addopts = -s -v`

---

## 14. 현재 관점에서의 구조적 특징

이 레거시 Python API는 다음 특성을 갖습니다.

### 장점

- 구조가 단순해서 전체 흐름 파악이 빠름
- SQLite 기반이라 로컬 재현이 쉬움
- 공통 응답/로깅 로직이 중앙화되어 있음

### 주의할 점

- CORS 및 에러 응답 처리가 프레임워크 기본 방식보다 커스텀 로직에 많이 의존
- 전역 환경 변수와 파일 시스템 상태에 영향을 많이 받음
- 응답 형식이 `output.py` 중심이므로 마이그레이션 시 동일 동작 보장이 필요
- 설치 스크립트와 런타임이 로컬 파일 구조에 결합되어 있음

---

## 15. 최신 TypeScript 구현과 비교할 때 참고할 포인트

현재 저장소 루트의 `src/` 는 TypeScript 기반 구현입니다.
레거시 `__LEGACY__/` 와 비교할 때 특히 확인해야 할 포인트는 다음과 같습니다.

- 엔드포인트 경로 호환성
- 공통 응답 포맷 호환성
- 에러 코드/헤더(`Error-Code`, `X-Process-Time`) 유지 여부
- 인증 방식 차이
- SQLite 기반 스키마/데이터 접근 방식 차이
- 파일 업로드 경로 및 저장 규칙 차이

---

## 16. 다음 분석 후보

필요하면 다음 주제를 이어서 분석할 수 있습니다.

1. `__LEGACY__/src/endpoints/` 전체 엔드포인트 맵 작성
2. 인증(`auth`) 흐름 상세 분석
3. 게시물/카테고리/태그 데이터 구조 분석
4. 파일 업로드/이미지 처리 흐름 분석
5. `resource/seed.sql` 기반 DB 스키마 분석
6. 현재 TypeScript 구현과의 기능 대응표 작성

---

## 참고 파일 목록

- `__LEGACY__/README.md`
- `__LEGACY__/pyproject.toml`
- `__LEGACY__/main.py`
- `__LEGACY__/install.py`
- `__LEGACY__/pytest.ini`
- `__LEGACY__/src/__init__.py`
- `__LEGACY__/src/api.py`
- `__LEGACY__/src/output.py`
- `__LEGACY__/src/libs/util.py`
- `__LEGACY__/src/modules/logger.py`
- `__LEGACY__/src/endpoints/get_home.py`
- `__LEGACY__/tests/__init__.py`
- `__LEGACY__/tests/conftest.py`
- `__LEGACY__/tests/home.py`

