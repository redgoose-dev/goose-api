# 구조와 실행 흐름

## 현재 아키텍처

개인용 CMS의 콘텐츠 API다. TypeScript/ESM 소스를 Bun으로 실행하고 Elysia로 HTTP·WebSocket 요청을 처리한다. 콘텐츠는 `bun:sqlite`, 파일·환경설정은 파일시스템에 저장한다. ORM, DI 컨테이너, 별도의 repository/service 계층은 없다.

```text
src/main.ts                 서버 조립과 실행
src/util.ts                 설치/삭제/비밀번호 초기화/캐시 정리 CLI
src/build.ts                서버와 CLI의 Bun 번들 빌드
src/classes/                상태 또는 연결을 가진 클래스
src/classes/provider/       인증 제공자 구현과 레지스트리
src/libs/                   공통 함수·정책·스키마·로깅
src/routes/<feature>/       기능별 라우트·스키마·핸들러·헬퍼
resource/seed.sql           새 콘텐츠 DB의 초기 스키마
resource/preference.json    초기 설정
```

`@/`는 `src/`를 가리킨다. 외부 패키지와 실제 버전 범위는 [package.json](../package.json), 설치 해석 결과는 `bun.lock`에 있다. 프레임워크와 인증 라이브러리 일부도 현재 `devDependencies`에 있고, 런타임 외부 의존성인 Sharp는 `dependencies`에 있다.

## 서버와 요청 흐름

1. [main.ts](../src/main.ts)에서 `Service`를 만들고 `setup()`을 기다린다. 설치 디렉터리·파일을 확인하고 저장된 preference를 읽는다. 미설치면 종료한다.
2. `service`를 Elysia store에 저장한다. 로그 제외 경로, 로깅 플러그인, 요청 추적, 응답·오류 훅을 연결한다.
3. `routes/index.ts`의 모듈들을 명시적인 `app.use()`로 등록한다. 자동 디렉터리 탐색은 하지 않는다.
4. Elysia가 요청 스키마를 검증하고 각 `__index.ts` 콜백이 인증 검사와 핸들러 호출을 연결한다.
5. 핸들러는 필요한 인자만 받아 DB·헬퍼·프로바이더를 호출한다. 라우트는 결과를 응답 계약에 맞게 반환한다.
6. 공통 훅은 헤더·오류 응답을 처리한다. 요청 추적은 완료/오류 시 정리하고 서버 중지 시 로그 대기열을 정리한다.

이것은 책임을 설명한 흐름이다. 모든 라우트가 인증을 요구하거나 같은 JSON으로 응답하는 것은 아니다.

## 책임 경계

| 위치 | 담당 | 새 코드에서 피할 배치 |
| --- | --- | --- |
| `__index.ts` | 경로/메서드/스키마 연결, 인증, 입력 전달, 응답 | 긴 SQL·암호 처리·파일 처리 |
| `__model.ts` | Elysia `t` 스키마와 `UnwrapSchema` 파생 타입 | DB 조회와 실행 시 부수효과 |
| `get-index.ts` 등 | 단일 작업의 처리 순서와 결과 | 라우트 등록, 서버 실행 |
| `__helper.ts` | 기능 안에서 재사용하는 조회·삭제·변환 | 관련 없는 범용 함수 모음 |
| `classes/` | DB 연결, Service 상태, 업로더, provider | 기능마다 관성적으로 추가하는 서비스 계층 |
| `libs/` | 여러 기능이 실제 공유하는 함수와 정책 | 한 엔드포인트만 쓰는 로직의 무조건 이동 |

기능 간 `__helper.ts` 참조는 현재 구조에 존재한다. 새 계층을 도입하기 전에 해당 헬퍼를 찾는다. `ctx` 전체 전달은 OAuth 콜백·파일 응답·mix처럼 HTTP 기능이 필요한 기존 예외에서 사용한다.

## 기능 지도

| 모듈 | 역할과 주의할 연결 |
| --- | --- |
| `home` | 서비스 정보와 privacy 페이지 |
| `app` → `nest` → `article` | 앱, 콘텐츠 묶음, 문서 계층. 삭제가 하위 헬퍼로 전파됨 |
| `category` | `module`/`module_srl` 기반 분류 및 순서 변경 |
| `comment` | article 댓글 |
| `checklist` | 진행률을 가진 항목. 조회도 기본 비공개 인증 |
| `json` | JSON 콘텐츠 |
| `tag` | 태그와 연결 테이블 관리 |
| `file` | 업로드·바이너리 응답·이미지 변환·캐시·연결 콘텐츠 권한 |
| `auth` | 로그인·갱신·프로바이더 관리·공개용 토큰·OAuth WebSocket |
| `preference` | Service 설정 조회·파일 저장 |
| `log` | 별도 로그 DB 조회·요약. 비공개 인증 |
| `mix` | 등록된 핸들러들을 순차 실행하고 앞 결과를 다음 입력에 참조 |
| `options_all.ts` | 공통 OPTIONS 처리 |

`mix`는 내부 HTTP 요청이 아니라 핸들러를 직접 호출한다. 따라서 원래 라우트의 스키마·인증 콜백이 자동 실행되지 않는다. 대상 핸들러 변경 시 [mix 매핑과 별도 인증](../src/routes/mix/__helper.ts)을 반드시 확인한다.

## 응답과 오류

조회·생성의 대표 응답은 `{ message, data }`, 목록 핸들러 결과는 `{ total, index }`다. 일부 수정·삭제는 문자열을 반환한다. 파일은 `Response`, OAuth는 redirect/문자열, mix는 `__message`를 포함한 별도 구조다. 공통 훅이 모든 응답을 JSON envelope로 변환하지 않는다.

업무 오류는 [ServiceError](../src/classes/ServiceError.ts)를 사용한다. [onErrorAfter](../src/libs/service.ts)는 401을 `Unauthorized`, 422를 `Invalid request data.`로 반환하고 301/302는 redirect로 처리한다. 기존 핸들러에는 데이터 없음에 204를 사용하는 패턴이 있다. 새 기능을 이유로 기존 상태 코드나 응답을 일괄 통일하지 않는다.
