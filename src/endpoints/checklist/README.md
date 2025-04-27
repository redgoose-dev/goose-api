# checklist

매일 기록하는 체크리스트 데이터입니다.


## put_item.py

새로운 체크리스트 데이터를 만듭니다.

### Request

```
PUT /checklist/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} content / 이름
@data {str} tag / 태그 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
@content {int} data / JSON srl 번호
```


## patch_item.py

체크리스트 데이터를 수정합니다.

### Request

```
PATCH /checklist/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 체크리스트 srl 번호
@data {str} content / 이름
@data {str} tag / 태그 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

체크리스트 데이터의 목록을 조회합니다.

### Request

```
@headers {str} Authorization / [required] 액세스 토큰
@query {str} content / 내용
@query {str} start / 등록일 시작 / ex) 2023-01-01
@query {str} end / 등록일 종료 / ex) 2023-12-30
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
@query {str} tag / 태그 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

체크리스트 데이터의 상세 정보를 조회합니다.

### Request

```
GET /checklist/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 체크리스트 srl 번호
@query {str} fields / 조회할 필드
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

체크리스트 데이터를 삭제합니다.

### Request

```
DELETE /checklist/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 체크리스트 srl 번호
```

### Response

```
@content {str} message / 메시지
```
