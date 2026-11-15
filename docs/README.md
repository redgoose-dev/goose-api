# goose-api 개발 문서

다른 에이전트와 개발자가 현재 구조를 이해하고 같은 방식으로 기능을 수정하기 위한 문서다. 설치·운영 명령은 [프로젝트 README](../README.md)를 참고한다.

분석 기준: 2026-11-15, 커밋 `bee48ab`, package version `2.1.16`. 되돌려진 패스키 구현은 현재 기능에 포함하지 않는다. 구현 사실과 향후 변경 지침을 구분하며, 이 문서는 새로운 아키텍처 제안서가 아니다.

## 읽는 순서

1. [구조와 실행 흐름](architecture.md): 책임 경계, 주요 기능, 변경 위치.
2. [코딩 컨벤션](coding-conventions.md): 실제 코드 스타일과 작성 예제.
3. [작업 절차와 검증](development-workflow.md): 변경 전후 확인과 완료 기준.
4. 작업에 따라 [인증과 프로바이더](auth-and-providers.md), [데이터와 저장소](data-and-storage.md)를 읽는다.
5. [분석 기준과 주의점](current-state.md)에서 기존 실패와 구현 예외를 확인한다.

## 문서 사용 원칙

- `.editorconfig`, `tsconfig.json`의 명시적 설정과 소스에서 관찰한 관례를 구분한다. 컨벤션 문서의 작업 규칙은 앞으로 수정할 코드에 적용한다.
- 기존 README는 기능별 참고 자료다. 특히 [기존 라우트 안내](../src/routes/README.md)의 엔드포인트 목록은 일부만 담고 있으므로 전체 목록으로 사용하지 않는다.
- 정확한 요청 필드는 각 기능의 `__model.ts`, URL·인증·응답은 `__index.ts`, DB 스키마는 `resource/seed.sql`을 확인한다. 모든 필드를 문서에 복제해 별도의 사양을 만들지 않는다.
- 구현과 문서를 함께 갱신한다. 분석 날짜만 바꾸고 검증 결과를 재사용하지 않는다.

## 작업별 출발점

| 작업 | 먼저 확인할 파일 |
| --- | --- |
| 일반 CRUD | [app 라우트](../src/routes/app/__index.ts), [모델](../src/routes/app/__model.ts), [생성 핸들러](../src/routes/app/put-item.ts) |
| 인증 제공자 | [provider 레지스트리](../src/classes/provider/index.ts), [auth 라우트](../src/routes/auth/__index.ts), [토큰 검사](../src/libs/verify.ts) |
| DB/관계/삭제 | [DB](../src/classes/DB.ts), [seed](../resource/seed.sql), 대상 기능 `__helper.ts` |
| 파일/이미지 | [FileUploader](../src/classes/FileUploader.ts), [file helper](../src/routes/file/__helper.ts), [파일 조회](../src/routes/file/get-item.ts) |
| 복합 요청 | [mix helper](../src/routes/mix/__helper.ts), [실행 핸들러](../src/routes/mix/post-index.ts) |
| 로그 | [로깅 README](../src/libs/logging/README.md), [로깅 모듈](../src/libs/logging/index.ts) |
| 설정/설치 | [Service](../src/classes/Service.ts), [CLI](../src/util.ts), [기본 설정](../resource/preference.json) |
