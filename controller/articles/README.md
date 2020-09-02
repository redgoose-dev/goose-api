# Controller / articles

가장 기초적인 요소이며 컨텐츠가 되는 서비스입니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get articles

여러개의 `article` 데이터들을 가져옵니다.

- url: `/articles/`
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| app | number | `1` | app srl |
| nest | number | `1` | nest srl |
| category | number | `1` | category srl |
| user | number | `1` | user srl |
| visible_type | string | `all` | 출력할 타입을 지정합니다. 값이 없으면 `public` 타입만 가져옵니다. |
| q | string | `toy` | 제목과 본문내용 검색어 |
| ext_field | string | `category_name` | 확장 필드 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `category_name`: 분류 이름을 가져옵니다.
- `nest_name`: 둥지 이름을 가져옵니다.
- `next_page`: 다음페이지 번호를 가져옵니다.


## get article

하나의 `article` 데이터를 가져옵니다.

- url: `/articles/[n]/` (n:srl)
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| app | number | `1` | app srl |
| nest | number | `1` | nest srl |
| hit | number | `0,1` | 이 항목을 `1`로 넣어서 사용하면 응답을 받을때 조회수가 올라갑니다. |
| visible_type | string | `all` | 출력할 타입을 지정합니다. 값이 없으면 `public` 타입만 가져옵니다. |
| ext_field | string | `category_name` | 확장 필드 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `category_name`: 분류 이름을 가져옵니다.
- `nest_name`: 분류 이름을 가져옵니다.


## add article

하나의 `article` 데이터 하나를 추가합니다.

- url: `/articles/`
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:----:|---------|-------------|
| app_srl | number | `1` | (required) app srl 번호 |
| nest_srl | number | `1` | (required) nest srl 번호 |
| category_srl | number | `1` | category srl 번호 |
| type | string | `public` | 글 타입 ('public','private') |
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| json | string | `{"foo", "bar"}` | json 데이터 |

- method: GET

사용하는 params 항목

| key | type | default | description |
|:---:|:----:|---------|-------------|
| content | string | `` | content 타입 |

### (GET) content

- ``: 일반적인 글을 작성하는데 사용되며 `markdown` 형식으로 사용합니다.
- `raw`: 소스 그대로 `content`항목의 내용을 저장합니다.


## edit article

하나의 `article` 데이터를 편집합니다.

- url: `/articles/[n]/edit/` (n:srl)
- method: POST

사용하는 body 항목

| key          | type   | value | example | description |
|:------------:|:------:|-------|---------|-------------|
| mode         | string | `add,edit` | `add` | 글 작성 방식 |
| category_srl | number |  | `1` | category srl 번호 |
| type         | string | `public,private` | `public` | 글 타입 |
| title        | string |  | `title name` | 글 제목 |
| content      | string |  | `content body text` | 글 본문 |
| hit          | number |  | `0` | 조회수 |
| star         | number |  | `0` | 좋아요 수 |
| json         | string |  | `{"foo", "bar"}` | 글 본문 |

- method: GET

사용하는 params 항목

| key | type | default | description |
|:---:|:----:|---------|-------------|
| content | string | `` | content 타입 |

### (GET) content

- ``: 일반적인 글을 작성하는데 사용되며 markdown용
- `raw`: 소스 그대로 저장합니다.


## delete article

하나의 `article` 데이터를 삭제합니다.

- url: `/articles/[n]/delete/` (n:srl)
- method: POST


## update hit|star

`조회수`, `좋아요` 값을 올려주는 역할을 합니다.

- url: `/articles/[n]/update/` (n:srl)
- method: POST

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| type | string | `hit,star` | 조회수를 올릴건지 좋아요 카운트를 올릴건지 정합니다. |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |


## change nest

선택한 article 데이터의 `nest_srl`을 바꿔줍니다.  
바뀐 `nest_srl`에 의하여 `app_srl`, `category_srl`값도 변하게 됩니다.

`nest_srl` 변경은 article 데이터에 큰 영향을 줄 수 있기 때문에 주의가 필요합니다.

- url: `/articles/[n]/change-nest/` (n:srl)
- method: POST

| name | type | example | description |
|:----:|:----:|---------|-------------|
| nest_srl | number | 1 | (required) 바꿀 `nest`의 번호입니다. |
| category_srl | number | 1 | 바꿀 `category_srl`의 값 |
