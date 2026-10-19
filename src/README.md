# 소스 코드 구조

`src/`는 API 서버, 관리용 CLI, 빌드 스크립트의 TypeScript 소스를 담고 있습니다.
설치와 실행 방법은 [프로젝트 README](../README.md)를 참고하세요.

## 디렉터리 구성

```text
src/
├── main.ts          # API 서버 진입점
├── util.ts          # 설치, 삭제, 비밀번호 재설정, 캐시 정리 CLI
├── build.ts         # 서버와 CLI 번들 빌드
├── global.d.ts      # 환경변수와 공유 타입 선언
├── classes/         # 서비스 상태, DB 접근, 파일 업로드 등
│   └── provider/    # 비밀번호 및 OAuth 인증 제공자
├── libs/            # 공통 함수와 설정
│   └── logging/     # 콘솔 출력, SQLite 기록, 요청 추적
└── routes/          # 기능별 라우트와 처리 코드
    ├── index.ts     # 라우트 모듈 내보내기
    ├── auth/        # 인증
    ├── article/     # 콘텐츠
    ├── file/        # 파일 및 이미지
    └── …            # app, nest, category, comment, log 등
```

`@/` 경로 별칭은 `src/`를 가리킵니다. 설정은 [tsconfig.json](../tsconfig.json)에 있습니다.

## 진입점과 실행 흐름

[main.ts](main.ts)는 `Service`를 생성하고 설치 상태와 저장된 설정을 읽습니다.
이후 Elysia 인스턴스에 서비스 상태, 로깅, 요청 추적, 공통 응답·오류 처리 훅을 연결하고,
[routes/index.ts](routes/index.ts)에서 가져온 라우트를 등록한 뒤 서버를 시작합니다.
서버가 종료될 때는 로그 기록 대기열을 정리하고 연결을 닫습니다.

[util.ts](util.ts)는 서버와 별도로 실행되는 관리용 CLI입니다.
초기 데이터와 계정을 생성하거나 기존 데이터, 비밀번호, 캐시를 관리합니다.
초기 데이터베이스 스키마와 기본 설정은 루트의 `resource/seed.sql`, `resource/preference.json`을 사용합니다.

[build.ts](build.ts)는 `main.ts`와 `util.ts`를 Bun으로 번들링해 `dist/`에 출력합니다.
Sharp는 번들에 포함하지 않고 실행 시 외부 의존성으로 사용합니다.

## 주요 모듈

| 위치 | 역할 |
| --- | --- |
| [classes/Service.ts](classes/Service.ts) | 설치 상태 확인, 서비스 설정과 공유 상태 관리 |
| [classes/DB.ts](classes/DB.ts) | 콘텐츠 SQLite 데이터베이스 접근 |
| [classes/DB_Log.ts](classes/DB_Log.ts), [classes/DB_LogReader.ts](classes/DB_LogReader.ts) | 로그 데이터베이스 기록 및 조회 |
| [classes/FileUploader.ts](classes/FileUploader.ts) | 파일 업로드 처리 |
| [classes/provider/](classes/provider/) | 비밀번호, Discord, GitHub, Google 인증 구현 |
| [libs/assets.ts](libs/assets.ts) | 공통 상수, 경로, 정책 값 |
| [libs/service.ts](libs/service.ts) | 공통 응답 및 오류 처리 |
| [libs/verify.ts](libs/verify.ts) | 인증 토큰 및 입력 검증 |
| [libs/logging/](libs/logging/README.md) | 요청 로그 출력, SQLite 기록, 요청 ID와 클라이언트 정보 추적 |

## 라우트 구성

`routes/`는 기능별 디렉터리로 구성합니다. 예를 들어 `article/`에는 다음 파일이 있습니다.

- [__index.ts](routes/article/__index.ts): URL과 HTTP 메서드를 등록하고 인증 검사 및 핸들러 호출을 연결합니다.
- [__model.ts](routes/article/__model.ts): 요청의 params, query, body 등에 사용할 스키마와 타입을 정의합니다.
- [__helper.ts](routes/article/__helper.ts): 해당 기능의 공통 처리 함수를 모읍니다.
- [get-index.ts](routes/article/get-index.ts), [get-item.ts](routes/article/get-item.ts), [patch-item.ts](routes/article/patch-item.ts) 등: 개별 요청의 데이터를 조회하거나 변경합니다.

새 라우트 모듈은 `routes/index.ts`에서 내보내고 `main.ts`의 `app.use()`에 등록합니다.
요청은 라우트의 스키마 검증과 인증 검사를 거쳐 처리 코드로 전달되며, 공통 훅에서 응답과 오류를 처리합니다.
기능별 동작은 [라우트 문서](routes/README.md)와 각 기능 디렉터리의 README를 참고하세요.
