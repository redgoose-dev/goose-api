# tag

아티클, JSON, 체크리스트 컨텐츠를 태깅하는 데이터를 다룹니다.  
데이터를 추가하거나 수정, 삭제하는 기능들을 


## get_index.py

태그 목록을 조회합니다.

### Request

```
GET /tag/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} module / 모듈 이름
@query {int} module_srl / 모듈 srl 번호
@query {str} name / 태그 이름
```

### Response

```
@content {str} message / 메시지
@content {int} data.total / 전체 데이터 수
@content {list} data.index / 데이터 목록
```


## put_item.py

새로운 태그를 추가합니다.

### Request

```
PUT /tag/

@headers {str} Authorization / [required] 액세스 토큰 
@data {str} module / [required] 모듈 이름
@data {int} module_srl / [required] 모듈 srl 번호
@data {str} tags / [required] 태그 이름 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
```


## patch_item.py

태그를 교체합니다. 새로운 태그는 추가되고 기존 태그는 삭제됩니다.

### Request

```
PATCH /tag/

@headers {str} Authorization / [required] 액세스 토큰 
@data {str} module / [required] 모듈 이름
@data {int} module_srl / [required] 모듈 srl 번호
@data {str} tags / [required] 태그 이름 / ex) tag1,tag2,tag3
```

### Response

```
@content {str} message / 메시지
```


## delete_item.py

태그를 삭제합니다.

### Request

```
DELETE /tag/

@headers {str} Authorization / [required] 액세스 토큰 
@data {str} module / [required] 모듈 이름
@data {int} module_srl / [required] 모듈 srl 번호
```

### Response

```
@content {str} message / 메시지
```
