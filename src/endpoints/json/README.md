# json

다목적으로 사용하기위한 데이터 트리를 관리하는 데이터


## put_item.py

새로운 JSON 데이터를 추가합니다.

### Request

```
PUT /json/

@headers {str} Authorization / [required] 액세스 토큰
@data {int} category / 카테고리 srl 번호
@data {str} name / [required] 이름
@data {str} description / 설명
@data {str} json / [required] JSON 데이터
@data {str} tag / 태그 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
@content {int} data / JSON srl 번호
```


## patch_item.py

JSON 데이터를 수정합니다.

### Request

```
PATCH /json/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] JSON srl 번호
@data {int} category / 카테고리 srl 번호
@data {str} name / 이름
@data {str} description / 설명
@data {str} json / JSON 데이터
@data {str} tag / 태그 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

JSON 데이터의 목록을 조회합니다.

### Request

```
GET /json/

@headers {str} Authorization / [required] 액세스 토큰
@query {int} category / 카테고리 srl 번호
@query {str} name / 이름
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
@query {str} tag / 태그 / ex) tag1,tag2,tag3
@query {str} mod / MOD (category)
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

JSON 데이터의 상세정보를 조회합니다.

### Request

```
GET /json/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] JSON srl 번호
@query {str} fields / 조회할 필드
@query {str} mod / MOD (count-file)
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

JSON 데이터를 삭제합니다. 삭제할 JSON에 속한 첨부파일, 태그 데이터들도 함께 삭제합니다.

### Request

```
DELETE /json/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] JSON srl 번호
```

### Response

```
@content {str} message / 메시지
```
