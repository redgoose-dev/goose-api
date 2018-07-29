# Controller / json

다목적으로 사용하기위한 데이터 트리를 관리하는 모듈입니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.

## get json list
- url: `/json`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| name | string | `foo` | filename |

## get json
- url: `/json/[n]` (n:srl)
- method: GET
- token level: public

## add json
- url: `/json`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| name | string | `name` | json의 이름 |
| description | string | `message` | comment |
| json | string | `{"foo": "bar"}` | json 데이터 |

## edit json
- url: `/json/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| name | string | `name` | json의 이름 |
| description | string | `message` | comment |
| json | string | `{"foo": "bar"}` | json 데이터 |

## delete json
- url: `/json/[n]/delete` (n:srl)
- method: POST
- token level: admin