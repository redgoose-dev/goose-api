# article

가장 기초적인 요소이며 컨텐츠 데이터입니다.


## put_item.py

새로운 `ready` 모드의 아티클을 만듭니다.  
이미 `ready` 모드의 데이터가 존재한다면 그 데이터의 srl 번호를 반환합니다.

### Request

```
PUT /article/

@headers {str} Authorization / [required] 액세스 토큰
```

### Response

```
@content {str} message / 메시지
@content {int} data / 아티클 srl 번호
```


## patch_item.py

아티클 데이터를 수정합니다.

### Request

```
PATCH /article/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 아티클 srl 번호
@data {int} app / 앱 srl 번호
@data {int} nest / 둥지 srl 번호
@data {int} category / 카테고리 srl 번호
@data {str} title / 제목
@data {str} content / 내용. 마크다운 형식으로 작성
@data {bool} hit=False / 조회수를 증가시킬지의 여부 (1,0)
@data {bool} star=False / 좋아요 수를 증가시킬지의 여부 (1,0)
@data {str} json / JSON 데이터
@data {str} tag / 태그 / ex) tag1,tag2,tag3
@data {str} mode / 모드 (public,private)
@data {str} regdate / 커스텀 등록일자 ex) 2023-10-01
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

아티클의 목록을 조회합니다.

### Request

```
GET /article/

@headers {str} Authorization / [required] 액세스 토큰
@query {int} app / 앱 srl 번호
@query {int} nest / 둥지 srl 번호
@query {int} category / 카테고리 srl 번호
@query {str} q / 제목과 내용의 키워드 검색
@query {str} mode / 모드 (public,private)
@query {str} duration / 기간 / ex) {new|old},{regdate},{day|week|month|year}
@query {str} random / 랜덤의 시드값 ex) 20240422
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
@query {str} tag / 태그 / ex) tag1,tag2,tag3
@query {str} mod / MOD (app,nest,category,tag)
```

- `duration`: 데이터 조회범위 `{시기},{필드}`
- `random`: 데이터 순서를 섞습니다. 순서를 고정시키기 위하여 시드값을 사용합니다. ex) 20240422

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

아티클의 상세 정보를 조회합니다.

### Request

```
GET /article/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 아티클 srl 번호
@query {str} fields / 조회할 필드
@query {str} mod / MOD (up-hit,up-star,app,nest,category,tag)
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

아티클을 삭제합니다. 삭제할 아티클에 속한 파일, 코멘트, 태그 데이터들도 함께 삭제합니다.

### Request

```
DELETE /article/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 아티클 srl 번호
```

### Response

```
@content {str} message / 메시지
```


## patch_change_srl.py

아티클의 앱, 둥지의 srl 번호를 변경합니다.

### Request

```
PATCH /article/{srl:int}/change-srl/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / 아티클 srl 번호
@data {int} nest / 둥지 srl 번호
```

### Response

```
@content {str} message / 메시지
```
