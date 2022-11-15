# Controller / json

다목적으로 사용하기위한 데이터 트리를 관리하는 모듈입니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get json list

- url: `/json/`
- method: GET

사용하는 파라메터 목록

| name      | type   | example | description              |
|:----------|:-------|---------|--------------------------|
| name      | string | `foo`   | filename                 |
| category  | int    | `1`     | category srl             |
| ext_field | string | ``      | 확장 필드                    |
| strict    | number | `0,1`   | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `category_name`: 분류 이름을 가져옵니다.


## get json

- url: `/json/[n]/` (n:srl)
- method: GET

사용하는 파라메터 목록

| name      | type   | example | description              |
|:----------|:-------|---------|--------------------------|
| ext_field | string | ``      | 확장 필드                    |
| strict    | number | `0,1`   | 일반 유저라면 자신만의 데이터를 가져옵니다. |

### ext_field
- `category_name`: 분류 이름을 가져옵니다.


## add json

- url: `/json/`
- method: POST

데이터를 추가할때 사용하는 body 항목

| key          | type   | example              | description  |
|:-------------|:-------|----------------------|--------------|
| category_srl | int    | `1`                  | category srl |
| name         | string | `name`               | json의 이름     |
| description  | string | `message`            | comment      |
| json         | string | `{"foo": "bar"}`     | json 데이터     |
| path         | string | `/path/filename.txt` | path         |


## edit json

- url: `/json/[n]/edit/` (n:srl)
- method: POST

데이터를 추가할때 사용하는 body 항목

| key          | type   | example              | description  |
|:-------------|:-------|----------------------|--------------|
| category_srl | int    | `1`                  | category srl |
| name         | string | `name`               | json 이름      |
| description  | string | `message`            | comment      |
| json         | string | `{"foo": "bar"}`     | json 데이터     |
| path         | string | `/path/filename.txt` | path         |


## delete json

- url: `/json/[n]/delete/` (n:srl)
- method: POST
