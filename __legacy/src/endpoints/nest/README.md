# nest

앱과 아티클 데이터 사이에서 그루핑해주는 데이터를 관리합니다.


## put_item.py

새로운 둥지를 추가합니다.

### Request

```
PUT /nest/

@headers {str} Authorization / [required] 액세스 토큰
@data {int} app / [required] 앱 srl 번호
@data {str} code / [required] 둥지의 코드 (UNIQUE)
@data {str} name / [required] 둥지의 이름
@data {str} description / 둥지의 설명
@data {str} json / 둥지의 JSON 데이터
```

### Response

```
@content {str} message / 메시지
@content {str} data / 앱 srl 번호
```


## patch_item.py

둥지 데이터를 수정합니다.

### Request

```
PATCH /nest/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 둥지 srl 번호
@data {int} app / 앱 srl 번호
@data {str} code / 둥지의 코드 (UNIQUE)
@data {str} name / 둥지의 이름
@data {str} description / 둥지의 설명
@data {str} json / 둥지의 JSON 데이터
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

둥지들의 목록을 조회합니다.

### Request

```
GET /nest/

@headers {str} Authorization / [required] 액세스 토큰
@query {int} app / 앱 srl 번호
@query {str} code / 둥지의 코드
@query {str} name / 둥지의 이름
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
@query {str} mod / MOD (app,count-article)
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

둥지의 상세 정보를 조회합니다.

### Request

```
GET /nest/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int|str} srl / [required] 둥지 srl 번호나 코드
@query {int} app / 앱 srl 번호
@query {str} fields / 조회할 필드
@query {str} mod / MOD ()
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

둥지를 삭제합니다.

### Request

```
DELETE /nest/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 둥지 srl 번호
```

### Response

```
@content {str} message / 메시지
```
