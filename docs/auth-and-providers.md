# 인증과 프로바이더

## 현재 제공하는 기능

[레지스트리](../src/classes/provider/index.ts)의 코드는 `password`, `discord`, `google`, `github`, 타입은 `password`, `oauth`다. 패스키 코드·클래스·라우트·credential 테이블은 현재 추적 소스에 없다. 로컬 잔여 테스트에서 기능을 역추론하지 않는다.

이 시스템에는 별도의 user 테이블이 없다. `provider` 행에 인증 제공자 코드와 계정 정보가 있고, `token.provider_srl`로 토큰을 연결한다. 일반적인 다중 사용자/RBAC 시스템이라고 가정하면 안 된다.

## 파일별 책임

| 파일 | 역할 |
| --- | --- |
| [Provider.ts](../src/classes/provider/Provider.ts) | access 토큰 축약, 만료 시간 변환, API authorize 링크 생성 |
| [provider/index.ts](../src/classes/provider/index.ts) | 코드·타입 상수, `getProvider()` 인스턴스 선택 |
| [Password.ts](../src/classes/provider/Password.ts) | bcrypt 비밀번호 검증, JWT 발급 |
| [Discord.ts](../src/classes/provider/Discord.ts), [Google.ts](../src/classes/provider/Google.ts), [Github.ts](../src/classes/provider/Github.ts) | 외부 OAuth URL, 토큰 교환·갱신, 계정 조회 |
| [auth/__index.ts](../src/routes/auth/__index.ts) | 인증 API와 스키마·권한 연결 |
| [auth/__model.ts](../src/routes/auth/__model.ts) | 요청 스키마·타입 |
| [verify.ts](../src/libs/verify.ts) | DB 토큰 검색·만료 검사 |

`Provider`는 abstract class이지만 공통 abstract 메서드 계약을 정의하지 않는다. `getProvider()`도 현재 `any`를 반환한다. 이름만 보고 모든 제공자가 동일한 메서드를 구현한다고 가정하지 않는다.

## 로그인과 갱신

프로바이더 응답과 새 password access JWT의 식별자는 `uid`다. HTTP 입력의 `id`는 `uid`로 매핑한다. 기존 `user_id` 응답/claim을 읽는 클라이언트는 `uid`로 변경해야 한다.

name/avatar/email은 선택값이다. password 등록은 이름 없이도 가능하지만 비밀번호는 필수다. OAuth 등록은 제공된 프로필만 저장하고 password 키를 추가하지 않는다. 응답은 문자열인 프로필 값만 포함하며 누락/null 필드를 강제로 채우지 않는다. password 로그인은 해시가 없는 계정을 401로 거부하며 OAuth 계정의 비밀번호 수정은 400으로 거부한다.

### 비밀번호

`POST /auth/login/` → `post-login.ts`에서 `(code, uid)` 조회 → bcrypt 검증 → `ProviderPassword.renewToken()` → 원본 access/refresh와 유효기간을 token 테이블에 저장 → `{ access, expires, refresh }` 반환.

클라이언트에 반환하는 access는 `Provider.getPublicToken()`으로 원본의 마지막 32자를 잘라낸 값이다. 이 메서드명의 public은 **읽기 전용 공개용 토큰이라는 권한 의미와 다르다**. 일반 로그인도 이 형태를 사용한다.

### OAuth

`GET /auth/redirect/:provider/`에서 외부 인증 URL을 구성한다. 콜백은 code를 토큰으로 교환하고 외부 계정 정보를 조회한다. `(code, uid)`에 해당하는 provider가 있으면 로그인하고, 없으면 state의 access token을 검사한 뒤 등록할 수 있다. 미등록 계정의 익명 콜백이 자동으로 계정을 생성하는 구조는 아니다.

콜백 결과는 state에 따라 redirect query 또는 WebSocket 메시지로 전달된다. `Service.data.oAuth`의 Map은 WebSocket과 타이머를 관리한다. `ws-authorize.ts`는 `AUTH_OPEN` → `AUTH_START` → `AUTH_LINK` 흐름을 처리하며, 콜백에서 `AUTH_COMPLETE`/`AUTH_ERROR`, 타임아웃에서 `AUTH_TIMEOUT`을 보낸다.

### 갱신·로그아웃

`POST /auth/renew/`는 refresh로 token 행을 찾되 `checkExpires: false`를 사용한다. `post-renew.ts`는 저장된 refresh 일치를 검사하고 해당 provider로 새 토큰을 발급한다. 트랜잭션에서 기존 토큰의 `expires`를 0으로 만들고 새 행을 추가한다. 로그아웃과 공개용 토큰 폐기도 만료 처리 경로를 확인해야 한다.

현재 Password 갱신 구현은 전달받은 refresh JWT의 서명/만료를 직접 검증하지 않고 새 토큰을 발급한다. 이를 안전성 보장으로 설명하거나 새 인증 방식에 그대로 복제하지 않는다. 수정한다면 기존 동작 변경과 회귀 검증 범위를 명시한다.

## 토큰 검사 계약

[checkingToken](../src/libs/verify.ts)은 기본적으로 DB 기반 검사다. 모든 요청에서 JWT 서명을 검증하는 미들웨어가 아니다.

- 입력 우선순위: 옵션 `accessToken`, 이후 `request.token`, query `_a`, `authorization` 헤더.
- 이 함수 자체에는 `Bearer ` 접두사를 제거하는 처리가 없다. 헤더 예제를 작성할 때 일반적인 Bearer 관례를 임의로 적용하지 않는다.
- access 검색은 `LIKE '%입력값'`으로 원본 토큰의 suffix를 찾는다. 길이·형식 검증이 보장된다고 가정하지 않는다.
- `expires`는 절대 timestamp가 아니라 `created_at` 기준 유효기간(초)다. 0은 폐기, NULL은 공개용 토큰의 저장 관례다.
- `usePublic: true`도 토큰을 요구한다. 인증 없는 공개 API라는 의미가 아니다. 현재 구현은 이 옵션이면 시간 경과 검사를 생략하므로, 만료된 일반 토큰도 별도 확인 대상이다.
- 기본 검사에서는 시간 경과를 검사한다. `useThrow: false`는 실패 시 null을 반환하지만 선언 반환 타입에는 이를 반영하지 않는다.

일반 읽기 라우트 다수가 `usePublic: true`, 쓰기는 기본 검사다. 예외로 checklist/log/preference는 읽기도 기본 검사, file 상세는 연결 콘텐츠·캐시에 따라 핸들러 내부에서 권한을 판단한다. `mix`는 별도 경로이므로 양쪽에서 검증한다.

## 프로바이더 관리

`POST /auth/ready-login/`는 DB에 등록된 제공자를 로그인 선택지로 보여 준다. `GET /auth/provider/`는 레지스트리와 DB 계정을 결합하며 미등록 OAuth 제공자의 사용 가능 여부와 연결 URL도 구성한다. 현재 두 경로 모두 code를 키로 계정을 묶으므로 동일 code의 여러 계정 표현을 자동 지원한다고 가정하지 않는다.

`PUT /auth/provider/`는 현재 비밀번호 제공자 전용이며 이미 password code가 있으면 거부한다. 수정 핸들러는 이름·이메일 등과 비밀번호 해시를 처리한다. 삭제는 provider를 지우고 관련 token의 `expires`를 0으로 바꾼다. 실제 FK 동작과 이 순서의 관계는 [저장소 문서](data-and-storage.md)를 참고한다.

## 새 인증 방식 추가 시 변경 지도

아래는 향후 작업 지침이며 패스키 설계나 구현을 확정한 내용이 아니다.

1. 기존 provider와 별도 인증 수단 중 무엇을 추가하는지 정하고, 기존 계정·토큰과 어떻게 연결되는지 설명한다.
2. `PROVIDER_CODE`, 필요하면 `PROVIDER_TYPE`, `getProvider()`와 해당 클래스에서 제공자 책임을 구성한다. 기존 OAuth URL 흐름에 모든 방식을 억지로 맞추지 않는다.
3. HTTP 요청은 auth의 개별 핸들러, 요청 스키마는 `__model.ts`, 경로·권한은 `__index.ts`에 추가한다.
4. ready-login, provider 목록·상세·수정·삭제, 토큰 갱신, CLI 계정 생성·비밀번호 초기화의 가정을 점검한다.
5. 새 영속 데이터가 필요하면 초기 seed와 기존 DB 전환을 구분한다. 기존 provider 컬럼을 통째로 교체하는 변경은 별도의 호환성 판단이 필요하다.
6. 일회성 인증 상태가 필요하면 수명·소유 세션·일회성 소비·정리 방법을 명시한다. `oAuth` Map은 현재 WebSocket 상태 공간이므로 용도가 다른 상태를 무조건 섞지 않는다.
7. 새 방식 성공뿐 아니라 기존 password/OAuth 로그인·갱신·폐기, 미인증 접근, 잘못된 입력, 재사용·만료, 비밀 정보 비노출을 검증한다.

새 기능 구현 중 발견한 기존 인증 문제를 조용히 대규모로 고치지 않는다. 필요한 보완과 독립된 개선을 구분해 영향 범위와 검증 결과를 남긴다.
