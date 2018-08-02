# Controller / articles

가장 기초적인 요소이며 컨텐츠가 되는 서비스입니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


### get articles list
- url: `/articles`
- method: GET
- token level: public

사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| app | number | `1` | app srl |
| nest | number | `1` | nest srl |
| category | number | `1` | category srl |
| user | number | `1` | user srl |
| title | string | `toy` | 제목 검색어 |
| content | string | `boy` | 본문내욕 검색어 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |
| ext_field | string | `category_name` | 확장 필드 |

#### ext_field
- `category_name`: 분류 이름을 가져옵니다.


### get article
- url: `/articles/[n]` (n:srl)
- method: GET
- token level: public

사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| hit | number | `0,1` | 이 항목을 `1`로 넣어서 사용하면 응답을 받을때 조회수가 올라갑니다. |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |
| ext_field | string | `category_name` | 확장 필드 |

#### ext_field
- `category_name`: 분류 이름을 가져옵니다.


### add article
- url: `/articles`
- method: POST
- token level: admin

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


### edit article
- url: `/articles/[n]/edit` (n:srl)
- method: POST
- token level: admin

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


### delete article
- url: `/articles/[n]/delete` (n:srl)
- method: POST
- token level: admin