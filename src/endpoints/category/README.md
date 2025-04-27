# category

둥지,JSON 데이터를 분류하는 데이터


## put_item.py

새로운 카테고리를 추가합니다.

### Request

```
PUT /category/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} module / [required] 모듈 이름 (nest,json)
@data {int} module_srl / [required] 모듈 srl 번호
@data {str} name / [required] 이름
```

### Response

```
@content {str} message / 메시지
@content {int} data / 카테고리 srl 번호
```


## patch_item.py

카테고리를 수정합니다.

### Request

```
PATCH /category/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 카테고리 srl 번호
@data {str} name / 이름
```

### Response

```
@content {str} message / 메시지
```


## patch_change_order.py

카테고리의 순서를 변경합니다.

### Request

```
PATCH /category/change-order/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} module / [required] 모듈 이름 (nest,json)
@data {int} module_srl / [required] 모듈 srl 번호
@data {int} srls / [required] 카테고리 srl 번호들 / ex) 1,2,3
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

카테고리 목록을 조회합니다.

### Request

```
GET /category/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} module / 모듈 이름 (nest,json)
@query {int} module_srl / 모듈 srl 번호
@query {str} name / 카테고리 이름
@query {str} q / 모듈의 제목과 설명 키워드 검색 / IF) mod=count, module
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
@query {str} mod / MOD (count, none,all) / IF) module
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

카테고리의 상세 정보를 조회합니다.

### Request

```
GET /category/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 카테고리 srl 번호
@query {str} fields / 조회할 필드
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

카테고리를 삭제합니다.

### Request

```
DELETE /category/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 카테고리 srl 번호
```

### Response

```
@content {str} message / 메시지
```
