# `OPTIONS` 요청 처리 분석

이 문서는 `__LEGACY__/` 경로의 Python FastAPI 프로젝트를 기준으로, `OPTIONS` 메서드 요청이 어떻게 처리되고 어떤 응답을 보내는지 정리한 문서입니다.

---

## 1. 결론 요약

레거시 API는 모든 경로에 대해 하나의 공통 `OPTIONS` 핸들러를 둡니다.

처리 결과는 다음과 같습니다.

- 상태 코드: `200`
- 본문: 빈 응답
- 주요 헤더:
  - `Access-Control-Allow-Origin: *`
  - `Access-Control-Allow-Methods: *`
  - `Access-Control-Allow-Headers: Content-Type, Authorization`
  - `Access-Control-Allow-Credentials: true`
  - `Access-Control-Max-Age: 86400`
- 별도 성공 로그 기록 안 함
- `X-Process-Time` 헤더 없음
- 디버그 출력도 `OPTIONS` 에 대해서는 생략됨

즉, 이 프로젝트는 `OPTIONS` 요청을 **CORS 프리플라이트 응답 전용 공통 엔드포인트**로 처리하고 있습니다.

---

## 2. 실제 처리 경로

### 2.1 FastAPI 전역 `OPTIONS` 라우트

파일: `__LEGACY__/src/api.py`

```python
@api.options('/{path_str:path}')
async def _options(path_str: str) -> Response:
    from .endpoints.options_any import preflight
    return await preflight(path_str)
```

의미:

- `/{path_str:path}` 패턴이므로 사실상 **모든 경로**에 대한 `OPTIONS` 요청을 받습니다.
- 예:
  - `OPTIONS /`
  - `OPTIONS /article`
  - `OPTIONS /article/123`
  - `OPTIONS /auth/provider/`
- 요청이 들어오면 `__LEGACY__/src/endpoints/options_any.py` 의 `preflight()` 로 전달합니다.

주의할 점:

- 라우트 함수는 `path_str` 만 받고 `Request` 객체를 받지 않습니다.
- 따라서 이후 응답 생성 시 요청 객체 기반 부가 정보(`X-Process-Time`, URL, method 기반 로그 등)를 넣지 않습니다.

---

## 3. 실제 응답 생성 코드

파일: `__LEGACY__/src/endpoints/options_any.py`

```python
from starlette.responses import Response
from .. import output

async def preflight(path_str: str) -> Response:
    return output.empty({
        'code': 200,
        'headers': {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': '*',
            'Access-Control-Allow-Headers': 'Content-Type, Authorization',
            'Access-Control-Max-Age': '86400',
        }
    }, _log=False)
```

핵심 포인트:

- `output.empty()` 사용
- 상태 코드 `200` 명시
- `_log=False` 이므로 성공 로그 기록 안 함
- `path_str` 인자는 받고 있지만 실제로는 사용하지 않음

즉, 경로마다 서로 다른 `OPTIONS` 정책을 두지 않고, **모든 경로에 동일한 정적 응답**을 반환합니다.

---

## 4. `output.empty()` 가 실제로 만드는 응답

파일: `__LEGACY__/src/output.py`

`output.empty()` 는 내부적으로 다음 순서로 동작합니다.

1. 상태 코드 결정
2. 기본 헤더와 사용자 헤더 병합
3. 필요 시 `X-Process-Time` 추가
4. 필요 시 성공 로그 기록
5. 빈 `Response` 반환

관련 기본 헤더는 다음과 같습니다.

```python
baseHeaders = {
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH',
    'Access-Control-Allow-Headers': 'Origin, Content-Type, Authorization, Accept',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Allow-Origin': '*',
}
```

그리고 헤더 병합은 다음 방식입니다.

```python
def __get_header__(src: Dict[str, str]) -> Dict[str, str]:
    return { **baseHeaders, **src }
```

즉:

- 공통 기본 헤더가 먼저 들어가고
- `preflight()` 에서 넘긴 헤더가 같은 키를 덮어씁니다.

그래서 최종적으로 `OPTIONS` 응답의 주요 헤더는 아래처럼 결정됩니다.

| 헤더 | 최종값 | 출처 |
|---|---|---|
| `Access-Control-Allow-Origin` | `*` | 기본값과 `preflight()` 값이 동일 |
| `Access-Control-Allow-Methods` | `*` | `preflight()` 가 기본값 덮어씀 |
| `Access-Control-Allow-Headers` | `Content-Type, Authorization` | `preflight()` 가 기본값 덮어씀 |
| `Access-Control-Allow-Credentials` | `true` | 기본 헤더에서 유지 |
| `Access-Control-Max-Age` | `86400` | `preflight()` 에서 추가 |

---

## 5. 실제 관찰 결과

코드 해석뿐 아니라 `TestClient` 로 `OPTIONS /` 요청을 실제로 확인한 결과는 다음과 같았습니다.

### 5.1 상태 코드

- `200`

### 5.2 헤더

```text
access-control-allow-credentials=true
access-control-allow-headers=Content-Type, Authorization
access-control-allow-methods=*
access-control-allow-origin=*
access-control-max-age=86400
content-length=0
```

### 5.3 본문

- 빈 문자열 (`''`)
- 사실상 body 없는 응답

즉, 실제 동작도 소스 코드 해석과 일치합니다.

---

## 6. `X-Process-Time` 이 없는 이유

일반 응답 생성 함수는 요청 객체(`Request`)를 받으면 `X-Process-Time` 헤더를 추가할 수 있습니다.

하지만 `OPTIONS` 처리 흐름은 다음과 같습니다.

- `api.py` 의 `_options()` 가 `Request` 를 받지 않음
- `preflight()` 도 `Request` 를 받지 않음
- `output.empty(..., _req=None)` 형태로 실행됨

`output.empty()` 내부에는 아래 조건이 있습니다.

```python
if _req: headers = __process_time__(_req, headers)
```

따라서 `OPTIONS` 응답에는 `X-Process-Time` 이 붙지 않습니다.

---

## 7. 로그가 남지 않는 이유

`preflight()` 는 다음처럼 호출됩니다.

```python
return output.empty({...}, _log=False)
```

`output.empty()` 는 `_log=True` 일 때만 성공 로그를 남깁니다.
그래서 `OPTIONS` 요청 자체는 별도 성공 로그를 남기지 않습니다.

또한 `__LEGACY__/src/api.py` 의 HTTP 미들웨어는 디버그 출력 조건을 이렇게 두고 있습니다.

```python
if __DEBUG__ and req.method != 'OPTIONS':
```

즉, `OPTIONS` 요청은:

- 일반 성공 로그 없음
- 디버그용 시작/종료 출력도 없음

결과적으로 **운영/개발 로그에서 상대적으로 조용하게 처리**되도록 설계되어 있습니다.

---

## 8. 경로별 차등 처리 여부

현재 구현에서는 경로에 따른 차등 응답이 없습니다.

이유:

- `path_str` 는 라우트에서 받지만
- `preflight(path_str)` 내부에서 사용하지 않음

즉, 아래 요청은 모두 동일한 정책으로 응답합니다.

- `OPTIONS /`
- `OPTIONS /app`
- `OPTIONS /article/10`
- `OPTIONS /auth/provider/`
- `OPTIONS /file/upload`

이 구조는 단순하지만, 특정 엔드포인트별 허용 메서드/헤더를 정교하게 제어하지는 못합니다.

---

## 9. 테스트 코드와의 불일치

파일: `__LEGACY__/tests/home.py`

```python
def test_preflight():
    res = client.options('/')
    assert res.status_code == 204
    assert res.headers['Access-Control-Allow-Origin'] == '*'
    assert res.headers['Access-Control-Allow-Methods'] == 'GET, POST, OPTIONS'
```

하지만 현재 실제 구현은 다음과 같습니다.

- 실제 상태 코드: `200`
- 실제 `Access-Control-Allow-Methods`: `*`

즉, **테스트 코드가 현재 소스와 불일치**합니다.

실제로 `uv run pytest tests/home.py -k preflight -q` 를 실행하면 아래 이유로 실패합니다.

- 기대값: `204`
- 실제값: `200`

이 불일치는 다음 가능성을 시사합니다.

1. 과거에는 `204` + 제한된 Allow-Methods 정책이었는데 구현이 바뀌었다.
2. 테스트가 업데이트되지 않았다.
3. CORS 정책을 단순화하는 과정에서 테스트 정합성을 놓쳤다.

문서 기준으로는 **현재 소스의 실제 동작은 `200` + `*`** 입니다.

---

## 10. 동작 흐름 요약

`OPTIONS /some/path` 요청이 들어오면 내부 흐름은 다음과 같습니다.

1. FastAPI 앱이 `@api.options('/{path_str:path}')` 라우트에 매칭
2. `_options(path_str)` 실행
3. `preflight(path_str)` 호출
4. `output.empty()` 로 빈 응답 생성
5. 상태 코드 `200` 과 CORS 헤더 반환
6. 로그는 남기지 않음
7. 응답 본문 없이 종료

요약하면 다음 한 줄로 표현할 수 있습니다.

> 레거시 API의 `OPTIONS` 요청은 모든 경로를 공통 프리플라이트 핸들러로 받아, 상태 코드 `200` 과 와일드카드 중심의 CORS 헤더를 담은 빈 응답을 반환한다.

---

## 11. 분석 대상 파일

- `__LEGACY__/src/api.py`
- `__LEGACY__/src/endpoints/options_any.py`
- `__LEGACY__/src/output.py`
- `__LEGACY__/tests/home.py`

---

## 12. 후속 작업 후보

필요하면 이어서 다음 문서도 정리할 수 있습니다.

1. `auth` 엔드포인트의 인증 헤더 처리 방식
2. 레거시 API 전체 CORS/응답 헤더 정책
3. 현재 TypeScript 구현과 레거시 `OPTIONS` 처리 비교
4. 실패 중인 레거시 테스트 정합성 분석

