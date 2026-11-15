# 분석 기준과 주의점

## 분석 범위

2026-11-15, HEAD `bee48ab`, package `2.1.16` 기준이다. 서버/CLI/빌드 진입점, DB·Service·provider 클래스, 공통 인증·응답, 라우트 등록과 모델, 대표 CRUD·관계 삭제·파일·mix·로깅 경로, 초기 스키마와 설정을 검토했다. 모든 엔드포인트의 실행 검증이나 보안 감사는 수행하지 않았다.

실제 `data/`의 내용과 `.env`/`.env.local`의 비밀 값은 읽거나 변경하지 않았다. 패스키 기능을 복원하거나 소스 코드를 수정하지 않았다.

## 검증 기준선

이 작업 환경의 Bun은 `1.4.4`, 로컬 TypeScript는 `7.0.2`다. 아래는 문서 작성 시 직접 실행한 결과이며 다른 환경의 결과를 보장하지 않는다.

| 검사 | 결과 |
| --- | --- |
| 작업 시작 `git status --short` | 출력 없음 |
| `git ls-files tests` | 추적 중인 테스트 없음 |
| `git check-ignore tests/passkey.test.ts` | `tests/` 규칙으로 무시되는 로컬 파일 확인 |
| `./node_modules/.bin/tsc --noEmit` | 종료 코드 1. 잔여 테스트에서 오류 4건 |
| `bun test` | 종료 코드 1. 모듈 로딩 오류로 0 pass, 1 fail, 1 error |

타입 오류 4건은 다음과 같다.

- `src/classes/Passkeys` 모듈 없음.
- `resource/migrate-provider-data` 모듈 없음.
- `src/classes/passkey-config` 모듈 없음.
- 테스트가 참조하는 `DB.connection` 속성이 현재 클래스에 없음.

테스트 실행도 `Passkeys` 모듈을 찾지 못해 중단되었다. `tests/passkey.test.ts`는 되돌린 기능을 참조하는 **현재 로컬의 무시된 잔여 파일**이며, 추적 저장소의 정상 동작 사양이 아니다. `tsconfig.json`은 tests를 검사에 포함하므로 Git 상태가 깨끗해도 타입 검사는 실패할 수 있다. 이 문서 작업에서는 잔여 테스트를 삭제·수정하거나 제외 설정을 바꾸지 않았다.

문서만 변경하므로 번들 빌드·서버 실행·실제 DB 동작 검증은 하지 않았다. 테스트 기준선 실패를 해결된 것으로 기록하지 않는다.

## 구현 사실과 권장 관례를 혼동하지 말 것

| 관찰 사항 | 향후 작업에서의 의미 | 근거 |
| --- | --- | --- |
| 일부 들여쓰기·세미콜론·중괄호 예외가 있음 | 예외를 새 코드 규칙으로 복제하거나 전체 재포맷하지 않음 | `MOD.ts`, `verify.ts`, `auth/put-token-item.ts` |
| 느슨한 `ZZ`/`any`와 전역 선언이 있음 | 새로운 계약은 가능한 명시하고 기존 공용 타입을 무작정 확대하지 않음 | `global.d.ts`, provider factory |
| `ServiceError`는 옵션 `cause`를 받지만 저장하거나 `super`에 전달하지 않음 | catch 패턴만 보고 원인 체인이 보존된다고 가정하지 않음 | `classes/ServiceError.ts` |
| 값 보간과 동적 field/order SQL이 있음 | 새 입력은 바인딩·식별자 검증을 사용 | `classes/DB.ts`, CRUD 핸들러 |
| `addData`는 falsy 값을 생략 | 0/false/빈 문자열 저장을 별도로 검증 | `classes/DB.ts` |
| seed의 FK 설정과 런타임 연결 설정이 다름 | 자동 cascade를 보장한다고 가정하지 않음 | `seed.sql`, `DB.#connect()` |
| mix가 핸들러를 직접 호출 | 일반 route의 스키마·권한이 자동 적용되지 않음. 현재 private 목록은 log 경로만 포함 | `mix/__helper.ts`, `mix/post-index.ts` |
| 토큰 검색은 suffix LIKE, usePublic은 시간 검사 생략 | 일반 JWT/Bearer 인증으로 설명하지 않고 인증 변경 시 별도 검증 | `libs/verify.ts` |
| Password 갱신에서 refresh 자체 검증 없음 | 새 인증 설계의 안전성 전제로 삼지 않음 | `provider/Password.ts`, `auth/post-renew.ts` |
| provider 목록을 code로 묶음 | DB의 복수 계정 가능성과 목록 표현을 구분 | `auth/get-provider-index.ts`, `post-ready-login.ts` |
| 초기 seed와 저장 preference를 자동 업그레이드하지 않음 | 기존 설치 대응을 별도로 설계 | `util.ts`, `Service.ts` |

이는 범위와 호환성을 판단하기 위한 관찰 목록이다. 문서 작성 과정에서 수정한 버그 목록도, 안전성이 검증된 전체 목록도 아니다. 관련 기능을 변경할 때 필요한 사항을 검증하고, 독립적인 개선은 범위를 분리한다.
