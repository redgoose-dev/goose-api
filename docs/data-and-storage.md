# 데이터와 저장소

## 저장 위치와 소유권

| 위치 | 내용과 관리 코드 |
| --- | --- |
| `PATH_DATA/db.sqlite` | 콘텐츠·provider·token, `classes/DB.ts` |
| `PATH_DATA/log.sqlite` | 요청 로그, `DB_Log.ts`/`DB_LogReader.ts` |
| `PATH_DATA/preference.json` | 실행 설정, `Service.loadPreference()`/`updatePreference()` |
| `PATH_DATA/upload/origin`, `upload/cover` | 업로드 파일, `FileUploader`와 file 헬퍼 |
| `PATH_DATA/cache` | 파일 메타데이터·변환 이미지 캐시 |
| `resource/seed.sql`, `resource/preference.json` | 새 설치에 사용하는 초기 원본 |

`libs/assets.ts`에는 기본 경로가 있지만 Service/CLI 일부는 `Bun.env.PATH_DATA`를 직접 사용한다. 환경변수를 생략해도 모든 경로가 동일한 fallback을 쓴다고 가정하지 않는다.

실제 `data/`와 `.env.local`을 문서 예제·테스트 fixture로 사용하지 않는다. 이번 문서 분석에서도 실제 DB 내용과 환경변수 비밀 값은 읽지 않았다.

## 데이터 관계

정확한 컬럼·제약·인덱스는 [seed.sql](../resource/seed.sql)이 기준이다.

| 테이블 | 의미와 관계 |
| --- | --- |
| `app` | 콘텐츠 앱, 고유 code |
| `nest` | app 소속 묶음, 고유 code |
| `article` | nest/category 연결, content/json, ready/public/private 상태 |
| `category` | module(`nest`/`json`) + module_srl로 분류 대상을 표현 |
| `json` | category와 연결되는 JSON 콘텐츠 |
| `checklist` | content와 0~100 percent |
| `comment` | module=`article`과 module_srl로 댓글 대상을 표현 |
| `file` | module(`article`/`checklist`/`comment`/`json`)과 module_srl, 경로·MIME |
| `tag`, `map_tag` | 태그와 module(`article`/`json`/`checklist`) 연결 |
| `provider` | `(code, user_id)` 고유 계정, 개별 user_* 컬럼 |
| `token` | provider_srl, 원본 access, expires, refresh |

`module`/`module_srl`은 여러 종류의 대상을 가리키므로 일반 FK가 모든 관계를 보장하지 않는다. `app.__helper.remove()` → nest → article → comment/tag/file처럼 애플리케이션 헬퍼가 삭제를 전파한다. 직접 행만 지우면 파일·연관 데이터가 남을 수 있다.

seed의 `PRAGMA foreign_keys = ON`은 설치 시 사용한 연결에 적용된다. 현재 `DB.#connect()`는 이 PRAGMA를 설정하지 않는다. seed의 `ON DELETE`만 보고 런타임 FK 정리를 보장한다고 판단하지 않는다.

## DB 접근 패턴

[DB.ts](../src/classes/DB.ts)는 lazy connection을 가진 싱글턴 `db`와 테이블 상수 `DB.TABLE`을 내보낸다. `getData`, `getIndex`, `getCount`, `addData`, `editData`, `deleteData`, `run`을 제공한다. 결과는 보통 `.data`를 통해 사용하며 SQL·values도 반환한다.

```typescript
const item = db.getData({
  table: DB.TABLE.PROVIDER,
  where: 'code = $code AND user_id = $userId',
  values: {
    '$code': code,
    '$userId': userId,
  },
})
```

- 새 외부 입력 값은 바인딩한다. `where`, `join`, `field`, `order`는 SQL 조각이므로 DB 래퍼가 안전성을 자동 보장하지 않는다.
- `where` 배열은 문자열을 이어 붙이고 선두 AND/OR를 제거한다. 기존 조건 배열의 결합 방식을 확인한다.
- pagination은 page/size가 모두 있고 양수일 때 적용한다. page=0이 빈 결과를 뜻한다고 가정하지 않는다.
- `DB.DATE_TIME`은 UTC ISO 형식 날짜를 생성하는 SQL 식이다. SQL 식은 `valueName`, 일반 값은 `value`를 사용한다.
- 현재 `addData()`는 truthy 값만 추가하므로 0/false/빈 문자열을 저장해야 할 때 주의한다. 원치 않는 기본값 적용 여부를 검사하고 필요한 경우 바인딩된 `run()` 등으로 명시적으로 처리한다.
- `getData()`는 타입상 `ZZ`지만 행이 없을 수 있다. 값 확인 없이 필드에 접근하지 않는다.

## 트랜잭션과 외부 부수효과

기존 삭제·갱신 패턴은 `_transaction = false` → begin 반환값 저장 → commit → catch에서 `rollback(_transaction)`이다. `DB.transaction()`이 반환하는 boolean은 현재 트랜잭션 여부 추적용이다.

싱글턴 연결이므로 중첩 트랜잭션이나 트랜잭션 사이의 비동기 요청 혼입을 자동 제어하지 않는다. 특히 네트워크 인증 작업을 DB 트랜잭션 안에서 오래 기다리는 구조를 추가하지 않는다. SQLite rollback은 이미 삭제한 파일이나 외부 서비스 동작을 되돌리지 않는다. DB와 파일을 함께 바꾸는 기능은 실패 시 정리 전략을 별도로 검토한다.

## 스키마 변경

현재 추적 파일에는 콘텐츠 DB용 범용 migration runner가 없다. seed 수정은 새 설치에만 적용된다. `install`은 기존 데이터를 비울 수 있으므로 마이그레이션 대용으로 실행하면 안 된다.

스키마 변경 작업에서는 다음을 명시한다.

1. 새 설치의 seed와 기존 DB의 전환 경로.
2. 데이터 보존·재실행·실패 시 처리 방법.
3. 기존 핸들러·CLI·토큰·관계·인덱스에 대한 영향.
4. 별도 임시 DB에서 새 설치와 기존 데이터 전환을 확인한 결과.

이 문서는 새 마이그레이션 프레임워크를 도입하라는 지시가 아니다. 변경에 필요한 최소 경로를 설계한다.

## 파일·설정·로그 변경 시

file 상세 조회는 숫자 srl 요청과 code 요청의 캐시 사용이 다르다. code의 메타데이터 캐시에는 권한 정보도 들어가므로 콘텐츠 공개 상태 변경과 캐시 무효화를 함께 확인한다. 이미지 변형 캐시만 보고 원본 메타데이터를 삭제하지 않는다. 캐시 정리는 CLI의 명시적 작업이다.

기본 preference를 수정해도 기존 설치의 저장 파일에 자동 병합되는 것은 아니다. `Service.setup()`은 저장된 설정을 읽고, `updatePreference()`는 change 인자에 따라 교체하거나 얕게 병합한다. 새 설정 키의 기존 설치 fallback을 검토한다.

콘텐츠 DB는 현재 DELETE journal, 로그 DB는 WAL과 batch queue를 사용한다. 로그 조회는 별도 reader 연결이다. 콘텐츠 DB 변경을 로그 DB에 기계적으로 적용하지 않는다. 로그 구조·보관 정책·요청 ID는 [로깅 문서](../src/libs/logging/README.md)를 참고한다.
