# app

클라이언트 서비스를 구분하는 데이터를 관리합니다.


## put_item.py

새로운 앱을 추가합니다.

### Request

```
PUT /app/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} code / [required] 앱 코드 (UNIQUE)
@data {str} name / [required] 앱 이름
@data {str} description / 앱 설명
```

### Response

```
@content {str} message / 메시지
@content {str} data / 앱 srl 번호
```


## patch_item.py

앱 데이터를 수정합니다.

### Request

```
PATCH /app/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 앱 srl 번호
@data {str} code / 앱 코드 (UNIQUE)
@data {str} name / 앱 이름
@data {str} description / 앱 설명
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

앱 데이터를 목록으로 조회합니다.

### Request

```
GET /app/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} code / 앱 코드
@query {str} name / 앱 이름
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
@query {str} mod / MOD (count-nest,count-article)
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

앱 데이터 상세 조회하기

### Request

```
GET /app/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int|str} srl / [required] 앱 srl 번호나 코드
@query {str} fields / 조회할 필드
@query {str} mod / MOD (count-nest,count-article)
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

앱 데이터 삭제하기.  
앱을 삭제하면 해당 앱에 속한 모든 데이터가 삭제됩니다. (둥지, 아티클)

### Request

```
DELETE /app/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 앱 srl 번호
```

### Response

```
@content {str} message / 메시지
```
