# `__LEGACY__` 요청 훅 정리

이 문서는 레거시 파이썬 프로젝트인 `__LEGACY__`에서 **클라이언트 요청이 들어온 뒤 라우터 처리 전에 어떤 일이 일어나는지**, 그리고 **엔드포인트 처리 후 응답을 보내기 전에 어떤 공통 처리가 수행되는지**를 정리한 문서입니다.

기준 파일:

- `__LEGACY__/main.py`
- `__LEGACY__/src/api.py`
- `__LEGACY__/src/output.py`
- `__LEGACY__/src/extends/Response.py`
- `__LEGACY__/src/modules/logger.py`
- `__LEGACY__/src/endpoints/*`

---

## 1. 진입점

`__LEGACY__/main.py`는 실제 앱 객체만 노출합니다.

- `from src.api import api`
- `app = api`

즉, 실제 요청/응답 훅은 대부분 `__LEGACY__/src/api.py`에 정의되어 있습니다.

---

## 2. 라우터 처리 전에 하는 일

핵심은 `__LEGACY__/src/api.py`의 전역 HTTP 미들웨어입니다.

### 2.1 전역 미들웨어

```python
@api.middleware('http')
async def _http(req: Request, call_next):
    now = None
    req.state.start_time = time.time()
    if __DEBUG__ and req.method != 'OPTIONS':
        now = datetime.now().strftime('%H:%M:%S')
        print(...)
    response = await call_next(req)
    if __DEBUG__ and req.method != 'OPTIONS':
        print(...)
    return response
```

### 2.2 실제 수행 작업

#### 1) 요청 시작 시간 기록
- `req.state.start_time = time.time()`
- 이후 응답 직전에 `X-Process-Time` 헤더를 만들 때 사용됩니다.

#### 2) 디버그 시작 로그 출력
- `__DEBUG__ == True`
- 그리고 HTTP method가 `OPTIONS`가 아닐 때만
- 콘솔에 `START | [METHOD] URL` 형태로 출력합니다.

#### 3) 실제 라우터/엔드포인트 실행
- `response = await call_next(req)`
- 여기서 FastAPI 라우팅, 파라미터 파싱, 엔드포인트 실행이 진행됩니다.

#### 4) 디버그 종료 로그 출력
- 엔드포인트 처리가 끝나고 응답 객체가 반환되면
- 콘솔에 `END` 로그를 출력합니다.

### 2.3 전역 전처리에서 하지 않는 것

이 미들웨어는 다음을 하지 않습니다.

- 전역 인증/권한 검사
- request body 변형
- 전역 DB 세션 주입
- 전역 CORS 미들웨어 처리

즉, 라우터 전 공통 처리는 거의 **요청 시간 측정 시작 + 디버그 로그 출력** 정도입니다.

---

## 3. 라우팅 단계에서 추가로 일어나는 일

### 3.1 라우터 등록

`__LEGACY__/src/api.py`에서 각 도메인 라우터를 등록합니다.

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

### 3.2 홈 라우트

- `GET /`는 `__LEGACY__/src/endpoints/get_home.py`로 연결됩니다.

### 3.3 OPTIONS 프리플라이트 처리

`__LEGACY__/src/api.py`에는 모든 경로에 대한 `OPTIONS` 핸들러가 있습니다.

```python
@api.options('/{path_str:path}')
async def _options(path_str: str) -> Response:
    from .endpoints.options_any import preflight
    return await preflight(path_str)
```

실제 구현은 `__LEGACY__/src/endpoints/options_any.py`에 있으며:

- `output.empty()`를 사용해 응답 생성
- 상태코드 `200`
- CORS 헤더 직접 지정
- `_log=False`라서 별도 성공 로그는 남기지 않음

---

## 4. 라우팅/검증 실패 시 전역 예외 처리

`__LEGACY__/src/api.py`에는 전역 예외 핸들러가 3개 있습니다.

### 4.1 `StarletteHTTPException`

```python
@api.exception_handler(StarletteHTTPException)
```

처리 방식:

- `output.error(...)`로 에러 응답 생성
- `405`는 특별히 `404`로 바꾸어 응답
- `405`일 때 메시지는 `'router error'`
- 그 외는 `e.detail` 사용

즉, 이 프로젝트는 **405를 사실상 404처럼 처리**합니다.

### 4.2 `RequestValidationError`

```python
@api.exception_handler(RequestValidationError)
```

처리 방식:

- 상태코드 `400`
- `method`, `path`를 포함
- `e.errors()`를 `stack`에 담아 `output.error(...)` 호출

즉, `Query`, `Form`, path parameter 검증 실패는 여기서 공통 처리됩니다.

### 4.3 `Exception`

```python
@api.exception_handler(Exception)
```

처리 방식:

- 상태코드 `500`
- `method`, `path` 포함
- `output.error('Exception Error', ...)` 호출

---

## 5. 엔드포인트 구현 구조

레거시 라우터는 보통 얇고, 실제 비즈니스 로직은 별도 함수 파일에 있습니다.

예: `__LEGACY__/src/endpoints/app/__init__.py`

```python
@router.get('/')
async def _get_index(req: Request, ...):
    from .get_index import get_index
    return await get_index({...}, req=req)
```

즉, 라우터 함수의 역할은 대체로 다음과 같습니다.

- FastAPI의 `Query`, `Form`, path parameter 선언
- 요청값을 dict로 정리
- 실제 구현 함수에 `req`와 함께 전달

실제 후처리는 라우터 자체보다는 구현 함수가 호출하는 `output.*()`에서 수행됩니다.

---

## 6. 엔드포인트 처리 후 응답 직전에 하는 일

이 프로젝트의 핵심 공통 후처리는 `__LEGACY__/src/output.py`에 있습니다.

대부분의 엔드포인트는 아래 패턴을 따릅니다.

```python
try:
    result = output.success(..., _req=req)
except Exception as e:
    result = output.exc(e, _req=req)
finally:
    return result
```

즉, 엔드포인트가 끝나고 응답을 만들 때 거의 항상 `output.success()`, `output.error()`, `output.empty()`, `output.buffer()`, `output.redirect()` 중 하나를 거칩니다.

---

## 7. `output.py`가 실제로 하는 일

### 7.1 기본 응답 헤더 추가

`__LEGACY__/src/output.py`에는 공통 헤더가 정의되어 있습니다.

```python
baseHeaders = {
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH',
    'Access-Control-Allow-Headers': 'Origin, Content-Type, Authorization, Accept',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Allow-Origin': '*',
}
```

대부분의 응답 헬퍼는 이 헤더를 기본으로 사용합니다.

의미:

- 별도의 CORS 미들웨어를 쓰기보다
- 응답 생성 시점에 직접 헤더를 붙이는 구조입니다.

### 7.2 처리 시간 계산

```python
def __process_time__(req: Request, headers: dict) -> str:
    if getattr(req.state, 'start_time', None) is None: return headers
    process_time = (time.time() - req.state.start_time) * 1000
    headers['X-Process-Time'] = f'{process_time:.2f} ms'
    return headers
```

의미:

- 미들웨어에서 저장한 `req.state.start_time` 사용
- 응답 헤더에 `X-Process-Time` 추가

즉, **시작 시각 기록은 `api.py` 미들웨어**, **실제 응답 헤더 추가는 `output.py`**가 담당합니다.

---

## 8. 응답 종류별 후처리

### 8.1 `output.success()`

성공 JSON 응답을 만들 때 사용합니다.

수행 작업:

1. 상태코드 결정
   - 기본값 `200`
2. 기본 CORS 헤더 병합
3. `_req`가 있으면 `X-Process-Time` 헤더 추가
4. 성공 로그 기록
5. `LocalJSONResponse` 반환

성공 로그에는 보통 다음 정보가 기록됩니다.

- message
- URL
- method
- module
- status_code
- user-agent
- ip
- run_time

### 8.2 `LocalJSONResponse`

`__LEGACY__/src/extends/Response.py`

- `JSONResponse`를 상속
- `json.dumps(..., ensure_ascii=False, indent=...)`
- UTF-8로 인코딩

의미:

- 한글이 유니코드 이스케이프가 아니라 그대로 출력됨
- indent 옵션으로 보기 좋은 JSON 포맷 가능

### 8.3 `output.error()`

에러 응답 생성 시 사용합니다.

수행 작업:

1. 에러 코드 계산
2. 기본 CORS 헤더 추가
3. 에러 메시지 결정
4. `404`, `405`가 아닌 경우:
   - 12자리 랜덤 `Error-Code` 헤더 생성
   - `X-Process-Time` 헤더 추가
   - 에러 로그 기록
5. 일반 `Response` 반환

특징:

- 성공 응답은 JSON 중심
- 에러 응답은 plain text `Response` 형태에 가깝습니다.

### 8.4 `output.empty()`

내용 없는 응답을 반환합니다.

수행 작업:

- 기본 상태코드 `204`
- 기본 CORS 헤더 추가
- `X-Process-Time` 추가
- 성공 로그 기록
- 빈 `Response` 반환

### 8.5 `output.buffer()`

파일/바이너리 응답용입니다.

수행 작업:

- 기본 CORS 헤더 추가
- `X-Process-Time` 추가
- 성공 로그 기록 (`'Open file'`)
- raw `Response` 반환

### 8.6 `output.redirect()`

리다이렉트 응답용입니다.

```python
def redirect(path: str, code: int = 302, _req: Request = None):
    return RedirectResponse(path, status_code=code)
```

특징:

- CORS 헤더 추가 없음
- `X-Process-Time` 추가 없음
- 로그 기록 없음

즉, 다른 응답 헬퍼와 다르게 후처리가 거의 없습니다.

### 8.7 `output.exc()`

예외를 공통 응답으로 바꿔주는 래퍼입니다.

동작:

- 예외 인자 두 번째 값이 `204`면 `output.empty()`
- 그 외는 `output.error(...)`
- `traceback.format_exc()`를 stack으로 넘김

즉, 엔드포인트의 `except Exception as e`는 대부분 여기로 수렴합니다.

---

## 9. 로깅 처리

로깅은 `__LEGACY__/src/modules/logger.py`가 담당합니다.

### 9.1 초기화

`__LEGACY__/src/api.py`에서 앱 시작 시 `logger.setup()`을 호출합니다.

환경 변수에 따라:

- 파일 로그 저장 여부
- stderr 출력 여부
- success/error 로그 활성화 여부

를 제어합니다.

### 9.2 성공 로그

`logger.success(...)`는 보통 아래 정보를 남깁니다.

- `method`
- `url`
- `module`
- `status_code`
- `ip`
- `user_agent`
- `run_time`

### 9.3 에러 로그

`logger.error(...)`는 성공 로그 정보에 더해:

- `error_code`
- `stack`

를 함께 기록합니다.

---

## 10. 전체 요청 수명주기

### 10.1 정상 요청

1. 클라이언트 요청 도착
2. `api.py` HTTP 미들웨어 진입
3. `req.state.start_time` 저장
4. 디버그 모드면 START 로그 출력
5. FastAPI 라우팅 수행
6. 파라미터 파싱/검증
7. 라우터 함수 실행
8. 실제 엔드포인트 구현 함수 실행
9. `output.success()` 등으로 응답 객체 생성
   - CORS 헤더 추가
   - `X-Process-Time` 추가
   - 성공 로그 기록
10. 미들웨어로 복귀
11. 디버그 모드면 END 로그 출력
12. 응답 전송

### 10.2 검증 실패 요청

1. 요청 도착
2. 미들웨어에서 시작 시간 저장
3. FastAPI 검증 실패
4. `RequestValidationError` 핸들러 실행
5. `output.error(...)`로 400 응답 생성
   - CORS 헤더
   - `X-Process-Time`
   - 에러 로그
6. 미들웨어 END 로그
7. 응답 전송

### 10.3 없는 라우트 / 메서드 불일치

1. 요청 도착
2. 미들웨어 진입
3. 라우팅 실패 또는 HTTP 예외 발생
4. `StarletteHTTPException` 핸들러 실행
5. `output.error(...)` 호출
   - `405`는 `404`로 변경
6. 미들웨어 END 로그
7. 응답 전송

---

## 11. 핵심 요약

### 요청 전(`before routing`)

주요 공통 작업은 `__LEGACY__/src/api.py`의 미들웨어에서 수행됩니다.

- 요청 시작 시간 저장
- 디버그 시작 로그 출력
- 이후 라우터/엔드포인트로 전달

### 응답 전(`after endpoint before response`)

주요 공통 작업은 `__LEGACY__/src/output.py`에서 수행됩니다.

- CORS 헤더 추가
- `X-Process-Time` 헤더 추가
- 성공/실패 로그 기록
- JSON/빈 응답/버퍼/에러 응답 객체 생성

### 구조적 특징

이 프로젝트는 응답 후처리를 **전역 미들웨어 하나에서 통합 처리하는 구조가 아니라**, 엔드포인트가 공통 `output.*()` 헬퍼를 호출하도록 하는 방식으로 구성되어 있습니다.

즉:

- **전역 미들웨어는 요청 시작 시점 처리 담당**
- **`output.py`는 응답 생성 직전 후처리 담당**

---

## 12. 참고 파일

- `__LEGACY__/main.py`
- `__LEGACY__/src/api.py`
- `__LEGACY__/src/output.py`
- `__LEGACY__/src/extends/Response.py`
- `__LEGACY__/src/modules/logger.py`
- `__LEGACY__/src/endpoints/get_home.py`
- `__LEGACY__/src/endpoints/options_any.py`
- `__LEGACY__/src/endpoints/app/__init__.py`
- `__LEGACY__/src/endpoints/app/get_index.py`

