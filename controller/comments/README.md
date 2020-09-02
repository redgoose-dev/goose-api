# Controller / comments

`article`글에서 사용하는 꼬리표를 붙이는 댓글을 사용하기 위하여 만들어진 모듈입니다.  
방문객이 사용하는것은 권장하지 않고, 글을 덧붙이는데 좋은 용도로 사용될 것입니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get comments

코멘트 목록을 가져옵니다.

- url: `/comments/`
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| article | int | `1` | article_srl |
| user | int | `1` | user_srl |
| q | string | `toy` | 본문내용 키워드 검색 |
| ext_field | string | `user_name` | 확장 필드 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `user_name`: 댓글 작성한 사람의 이름을 가져옵니다.


## get comment

코멘트 하나를 가져옵니다.

- url: `/comments/[n]/` (n:srl)
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| ext_field | string | `user_name` | 확장 필드 |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `user_name`: 댓글 작성한 사람의 이름을 가져옵니다.


## add comment

코멘트를 추가합니다.

- url: `/comments/`
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:----:|---------|-------------|
| article_srl | number | `1` | article srl 번호 |
| user_srl | number | `1` | 필요할때 임의로 넣을 수 있는 유저번호 |
| content | string | `content body text` | 글 본문 |
| get | number | `0,1` | 코멘트를 추가하고 그 데이터를 출력합니다. |


## edit comment

코멘트 하나를 수정합니다.

- url: `/articles/[n]/edit/` (n:srl)
- method: POST

사용하는 body 항목

| key | type | example | description |
|:---:|:----:|---------|-------------|
| article_srl | number | `1` | article srl 번호 |
| user_srl | number | `1` | user srl 번호 |
| content | string | `content body text` | 글 본문 |


## delete comments

코멘트를 삭제합니다.

- url: `/comments/[n]/delete/` (n:srl)
- method: POST
