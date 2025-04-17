# file

첨부파일 데이터


## put_item.py

파일을 업로드하고 데이터를 추가합니다.

### Request

```
PUT /file/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} module / [required] 모듈 이름 (article,json,checklist,comment)
@data {int} module_srl / [required] 모듈 srl 번호
@data {str} dir_name='origin' / 업로드 디렉토리 이름
@data {File} file / [required] 파일
@data {str} json / JSON 데이터
@data {str} format / 이미지 포맷 (image/jpeg,image/png,image/webp,image/avif)
@data {int} quality=90 / 이미지 품질 (0~100)
```

### Response

```
@content {str} message / 메시지
@content {int} data / 파일 srl 번호
```


## patch_item.py

파일을 교체하거나 정보를 수정합니다.

### Request

```
PATCH /file/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 파일 srl 번호
@data {str} module / 모듈 이름 (article,json,checklist,comment)
@data {int} module_srl / 모듈 srl 번호
@data {str} dir_name='origin' / 업로드 디렉토리 이름
@data {File} file / 파일
@data {str} json / JSON 데이터
@data {str} format / 이미지 포맷 (image/jpeg,image/png,image/webp,image/avif)
@data {int} quality=90 / 이미지 품질 (0~100)
```

### Response

```
@content {str} message / 메시지
```


## get_index.py

파일 목록을 조회합니다.

### Request

```
GET /file/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} module / 모듈 이름 (article,json,checklist,comment)
@query {int} module_srl / 모듈 srl 번호
@query {str} name / 이름
@query {str} mime / MIME 타입
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

해당되는 첨부파일을 엽니다.

### Request

```
GET /file/{srl:int|str}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int|str} srl / [required] 파일 srl 번호나 코드
@query {int} w / 이미지 width
@query {int} h / 이미지 height
@query {int} t / 이미지 리사이즈 방식 (contain,stretch,cover)
@query {int} q / 이미지 품질 (0~100)
```

### Response

```
@headers {str} Content-Type / MIME 타입
@headers {str} Content-Length / 파일 크기
@content {buffer} / 파일 데이터
```


## delete_item.py

파일을 삭제합니다.

### Request

```
DELETE /file/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 파일 srl 번호
```

### Response

```
@content {str} message / 메시지
```
