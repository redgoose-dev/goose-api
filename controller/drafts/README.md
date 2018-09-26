# Controller / drafts

`article` 포스트하기전에 임시로 저장하기 위한 용도로 사용됩니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get drafts list
- url: `/drafts`
- method: GET


## get draft
- url: `/drafts/[n]` (n:srl)
- method: GET


## add draft
- url: `/drafts`
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:---:|---|---|
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| json | string | `{"foo", "bar"}` | json 데이터 |
| description | string | `description text` | description |

- method: GET

사용하는 params 항목

| key | type | default | description |
|:---:|:---:|---|---|
| content | string | `` | content 타입 |

### (GET) content

- ``: 일반적인 글을 작성하는데 사용되며 markdown용
- `raw`: 소스 그대로 `content`항목의 내용을 저장합니다.


## edit draft
- url: `/drafts/[n]/edit` (n:srl)
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:---:|---|---|
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| json | string | `{"foo", "bar"}` | json 데이터 |
| description | string | `description text` | description |

- method: GET

사용하는 params 항목

| key | type | default | description |
|:---:|:---:|---|---|
| content | string | `` | content 타입 |

### (GET) content

- ``: 일반적인 글을 작성하는데 사용되며 markdown용
- `raw`: 소스 그대로 저장합니다.


## delete draft
- url: `/drafts/[n]/delete` (n:srl)
- method: POST
