# Log

`PATH_DATA/log.sqlite`에 기록된 서비스 로그를 조회합니다.
모든 엔드포인트는 액세스 토큰 인증이 필요합니다.


## get-index.ts

로그 목록을 최신순으로 조회합니다.

### Request

```
GET /log/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} cursor / 이전 응답의 data.assets.cursor
@query {int} size=50 / 조회할 로그 수 (1~100)
@query {int} total / `1`이면 검색 조건에 해당하는 전체 로그 수를 포함
@query {str} level / 로그 레벨 / ex) ERROR,WARNING
@query {str} from / 조회 시작일 (YYYY-MM-DD)
@query {str} to / 조회 종료일 (YYYY-MM-DD)
@query {int} status / HTTP 상태 코드 (100~599)
@query {str} method / HTTP 요청 메서드
@query {str} path / 요청 경로에 포함되는 문자열
@query {str} request_id / 요청 ID
@query {str} q / 메시지, 오류 메시지, 요청 경로, 요청 ID 검색
```

날짜는 UTC 기준으로 처리합니다. `from`은 해당 날짜의 `00:00:00.000Z`,
`to`는 `23:59:59.999Z`로 적용합니다.

목록은 `timestamp DESC, id DESC` 순서로 조회합니다. 다음 목록이 있으면
`data.assets.cursor`를 다음 요청의 `cursor`로 사용합니다.
`total=1`일 때의 전체 개수는 `cursor`와 `size`를 제외한 검색 조건을 기준으로 합니다.

목록에는 오류 stack, cause, context를 포함하지 않습니다. 해당 필드는
상세조회에서 확인할 수 있습니다.

### Response

```
@content {str} message / 메시지
@content {list} data.index / 로그 목록
@content {int} data.total / total=1일 때 검색 조건에 해당하는 전체 로그 수
@content {bool} data.assets.has_next / 다음 목록 존재 여부
@content {str|null} data.assets.cursor / 다음 목록 조회 커서
```


## get-item.ts

로그의 상세정보를 조회합니다.

### Request

```
GET /log/{id:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} id / [required] 로그 ID
```

### Response

```
@content {str} message / 메시지
@content {dict} data / 로그 상세정보
@content {dict|null} data.context / 로그 context
@content {dict|null} data.error / 오류 메시지, stack, cause
```


## get-summary.ts

지정한 기간의 로그를 레벨, HTTP 상태, 시간 단위로 집계합니다.

### Request

```
GET /log/summary/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} from / 집계 시작일 (YYYY-MM-DD), 생략하면 최근 24시간
@query {str} to / 집계 종료일 (YYYY-MM-DD)
@query {str} interval / 집계 단위 (hour,day), 생략하면 기간에 따라 자동 선택
```

`from`, `to`를 모두 생략하면 현재 시각을 기준으로 최근 24시간을 집계합니다.
실제 집계 범위와 단위는 `data.assets`에 포함합니다.

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 로그 수
@content {dict} data.levels / 레벨별 로그 수
@content {dict} data.statuses / HTTP 상태 그룹별 로그 수
@content {dict} data.duration_ms / 평균, 최대 처리시간
@content {str|null} data.latest_error_at / 최근 오류 발생시각
@content {list} data.timeline / 시간 단위별 로그 수
@content {dict} data.assets / 집계 시작일, 종료일, 집계 단위
```
