# mix

여러가지 요청을 한꺼번에 보낼 수 있습니다.
클라이언트에서 요청을 여러번 보낼때 많은 부담이 생기기 때문에 꼭 만들어보고 싶었던 기능입니다.


## post_main.py

### Request

기초적인 요청하는 방법은 다음과 같습니다.
요청할때의 JSON 데이터는 `요청 JSON 예제` 섹션을 참고해주세요.

```
POST /mix/

@headers {str} Authorization / [required] 액세스 토큰
@json {list} / [required] 요청할 데이터
```

### Response

```
@content {dict} 응답내용. 요청한 `key`를 기준으로 key-value 쌍으로 응답합니다.
```


## 사용할 수 있는 엔드포인트

다음은 mix 엔드포인트에서 사용할 수 있는 API 엔드포인트 목록입니다.

| Method | URL               | Description     |
|--------|-------------------|-----------------|
| GET    | /                 | 홈               |
| GET    | /app/             | 앱 목록 조회         |
| GET    | /app/{srl}/       | 앱 상세 데이터 조회     |
| GET    | /article/         | 아티클 목록 조회       |
| GET    | /article/{srl}/   | 아티클 상세 데이터 조회   |
| GET    | /category/        | 카테고리 목록 조회      |
| GET    | /category/{srl}/  | 카테고리 상세 데이터 조회  |
| GET    | /checklist/       | 체크리스트 목록 조회     |
| GET    | /checklist/{srl}/ | 체크리스트 상세 데이터 조회 |
| GET    | /comment/         | 댓글 목록 조회        |
| GET    | /comment/{srl}/   | 댓글 상세 데이터 조회    |
| GET    | /file/            | 파일 목록 조회        |
| GET    | /json/            | JSON 목록 조회      |
| GET    | /json/{srl}/      | JSON 상세 데이터 조회  |
| GET    | /nest/            | 둥지 목록 조회        |
| GET    | /nest/{srl}/      | 둥지 상세 데이터 조회    |


## 요청 JSON 예제

기능의 한계상(많은 기능을 집어넣으면 과도한 복잡성이 증가하게 됩니다) 예외 처리나 커스터마이즈한 형태의 요청은 제한적입니다.  
그래서 단순한 형태나 예외가 적은 형태로 충분히 테스트를 거친후에 사용할것을 권장드립니다.

> 주의할점은 주로 데이터를 조회하는 용도로 사용하며 파일 업로드나 복잡한 요청은 피해주세요.

다음은 다양한 예제로 요청하고 응답받는 모습으로 예시를 보여드리겠습니다.

### 예제 1) home

Request

```json
[
  {
    "key": "home",
    "url": "/"
  }
]
```

Response

```json
{
  "home": {
    "message": "Hello! GOOSE-API",
    "version": "2.0.0",
    "dev": true,
    "status_code": 200
  }
}
```

### 예제 2) APP / 목록과 목록의 첫번째 데이터 조회하기

#### Request

```json
[
  {
    "key": "app-index",
    "url": "/app/",
    "params": {
      "size": "3"
    }
  },
  {
    "key": "app-item",
    "url": "/app/{srl}/",
    "params": {
      "srl": "{{app-index.data.index[0].srl}}"
    }
  }
]
```

단순히 요청만 보낸다면 사용성의 한계가 있기 때문에 이전 요청의 결과를 다음 요청에 활용할 수 있도록 만들었습니다.  
`{{KEY_NAME.DATA_PATH}}` 형태로 이전 요청의 결과를 다음 요청에서 사용할 수 있습니다. `{{app-index.data.index[0].srl}}` 부분을 참고해주세요.

#### Response

```json
{
  "app-index": {
    "message": "Complete get app index.",
    "data": {
      "total": 8,
      "index": [
        {
          "srl": 10,
          "code": "University-of-Georgia",
          "name": "죠지아 대학교",
          "description": "Et docere et rerum exquirere causas.",
          "created_at": "2025-04-11 11:28:08"
        },
        {
          "srl": 9,
          "code": "rocky_mountain",
          "name": "로키오키",
          "description": "ㄷㄷㄷㄷㄷㄷ",
          "created_at": "2025-04-11 11:27:13"
        }
      ]
    },
    "status_code": 200
  },
  "app-item": {
    "message": "Complete get app item.",
    "data": {
      "srl": 10,
      "code": "University-of-Georgia",
      "name": "죠지아 대학교",
      "description": "Et docere et rerum exquirere causas.",
      "created_at": "2025-04-11 11:28:08"
    },
    "status_code": 200
  }
}
```

### 예제 3) 일부 요청에서 오류가 발생했을때

요청 보냈을때 오류가 발생했다면 응답에서 해당되는 키 값에서 status_code와 message를 확인할 수 있습니다.

#### Request

```json
[
  {
    "key": "home",
    "url": "/"
  },
  {
    "key": "not-found",
    "url": "/xxx/"
  }
]
```

#### Response

```json
{
  "home": {
    "message": "Service Error",
    "status_code": 500
  },
  "not-found": null
}
```


## 파라메터 키값 ALIAS

일반적인 API 요청할때의 파라메터 키 값과 함수에서 사용되는 키값이 다를 수 있습니다.  
주로 파이썬 예약어 때문에 다른 이름으로 파라메터 값으로 받아왔는데 mix에서는 함수에 직접 전달하기 때문에 몇몇 키 값이 다릅니다.  
다음 표를 참고해주세요.

| API key    | mix key         |
|------------|-----------------|
| `app`      | `app_srl`       |
| `nest`     | `nest_srl`      |
| `category` | `category_srl`  |
| `json`     | `json_data`     |
| `token`    | `access_token`  |
| `refresh`  | `refresh_token` |
| `id`       | `user_id`       |
| `password` | `user_password` |
| `name`     | `user_name`     |
| `avatar`   | `user_avatar`   |
| `email`    | `user_email`    |
| `format`   | `file_format`   |
| `quality`  | `file_quality`  |
| `change`   | `change_data`   |
