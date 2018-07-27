# Controller / nests

article 들을 그루핑하는 역할을 합니다.  
`categories`도 같은 기능을 가지고 있지만 `nests`는 더 폭넓게 그루핑을 하거나 더 많은 기능을 가지고 있습니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.

## get nests list
- url: `/nests`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| app | number | `1` | app srl |
| id | string | `hello` | 고유 id값 |
| name | string | `Hello nest` | filename |

## get nest
- url: `/nests/[n]` (n:srl)
- method: GET
- token level: public

## add nest
- url: `/nests`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl |
| id | string | `hello` | nest id |
| name | string | `hello app` | name |
| description | string | `memo` | description |
| json | string | `{"foo": "bar"}` | json data |

## edit nest
- url: `/nests/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl |
| id | string | `hello` | nest id |
| name | string | `hello app` | name |
| description | string | `memo` | description |
| json | string | `{"foo": "bar"}` | json data |

## delete nest
- url: `/nests/[n]/delete` (n:srl)
- method: POST
- token level: admin