# comment

아티클 게시물에 대한 댓글을 관리합니다.


## put_item.py

새로운 댓글을 추가합니다.

### Request

```
PUT /comment/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} module / [required] 모듈 이름 (article)
@data {int} module_srl / [required] 모듈 srl 번호
@data {str} content / [required] 내용 (:마크다운)
```

### Response

```
@content {str} message / 메시지
@content {dict} data / 댓글 데이터
```


## patch_item.py

댓글을 수정합니다.

### Request

```
PATCH /comment/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 댓글 srl 번호
@data {str} module / 모듈 이름 (article)
@data {int} module_srl / 모듈 srl 번호
@data {str} content / 내용 (:마크다운)
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

댓글 목록을 조회합니다.

### Request

```
GET /comment/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} module / 모듈 이름 (article)
@query {int} module_srl / 모듈 srl 번호
@query {str} q / 본문내용 키워드 검색
@query {str} fields / 조회할 필드
@query {int} page / 페이지 번호
@query {int} size / 페이지 당 데이터 수
@query {str} order='srl' / 정렬 기준
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {bool} unlimited=False / 무제한 조회 여부 (1=무제한, 0=제한)
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## get_item.py

댓글의 상세 정보를 조회합니다.

### Request

```
GET /comment/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 댓글 srl 번호
@query {str} fields / 조회할 필드
```

### Response

```
@content {str} message / 메시지
@content {int} data / 데이터
```


## delete_item.py

댓글을 삭제합니다.

### Request

```
DELETE /comment/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 댓글 srl 번호
```

### Response

```
@content {str} message / 메시지
```
