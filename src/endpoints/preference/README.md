# preference

GOOSE-API 환경설정 데이터


## get_main.py

환경설정 데이터를 조회합니다.

### Request

```
GET /preference/

@headers {str} Authorization / [required] 액세스 토큰
```

### Response

```
@content {str} message / 메시지
@content {str} data / 환경설정 데이터
```


## patch_main.py

환경설정 데이터를 수정합니다.

### Request

```
PATCH /preference/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} json / 환경설정 데이터
@data {bool} change / 데이터 교체 여부 (1=교체, 0=덮어쓰기)
```

### Response

```
@content {str} message / 메시지
@content {str} data / 업데이트된 환경설정 데이터
```
