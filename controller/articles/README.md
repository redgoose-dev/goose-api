# Controller / articles

가장 기초적인 요소이며 컨텐츠가 되는 서비스입니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get articles list
- url: `/articles`
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| app | number | `1` | app srl |
| nest | number | `1` | nest srl |
| category | number | `1` | category srl |
| user | number | `1` | user srl |
| title | string | `toy` | 제목 검색어 |
| content | string | `boy` | 본문내욕 검색어 |
| ext_field | string | `category_name` | 확장 필드 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `category_name`: 분류 이름을 가져옵니다.
- `next_page`: 다음페이지 번호를 가져옵니다.


## get article
- url: `/articles/[n]` (n:srl)
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| hit | number | `0,1` | 이 항목을 `1`로 넣어서 사용하면 응답을 받을때 조회수가 올라갑니다. |
| ext_field | string | `category_name` | 확장 필드 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `category_name`: 분류 이름을 가져옵니다.


## add article
- url: `/articles`
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl 번호 |
| nest_srl | number | `1` | nest srl 번호 |
| category_srl | number | `1` | category srl 번호 |
| user_srl | number | `1` | user srl 번호 |
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| json | string | `{"foo", "bar"}` | 글 본문 |

- method: GET

사용하는 params 항목

| key | type | default | description |
|:---:|:---:|---|---|
| content | string | `` | content 타입 |

### (GET) content

- ``: 일반적인 글을 작성하는데 사용되며 markdown용
- `raw`: 소스 그대로 저장합니다.


## edit article
- url: `/articles/[n]/edit` (n:srl)
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl 번호 |
| nest_srl | number | `1` | nest srl 번호 |
| category_srl | number | `1` | category srl 번호 |
| user_srl | number | `1` | user srl 번호 |
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| hit | number | `0` | 조회수 |
| star | number | `0` | 좋아요 수 |
| json | string | `{"foo", "bar"}` | 글 본문 |

- method: GET

사용하는 params 항목

| key | type | default | description |
|:---:|:---:|---|---|
| content | string | `` | content 타입 |

### (GET) content

- ``: 일반적인 글을 작성하는데 사용되며 markdown용
- `raw`: 소스 그대로 저장합니다.


## delete article
- url: `/articles/[n]/delete` (n:srl)
- method: POST


## update hit|star
- url: `/articles/[n]/update` (n:srl)
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| type | string | `hit,star` | 조회수를 올릴건지 좋아요 카운트를 올릴건지 정합니다. |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |