# Controller / nests

article 들을 그루핑하는 역할을 합니다.  
`categories`도 같은 기능을 가지고 있지만 `nests`는 더 폭넓게 그루핑을 하거나 더 많은 기능을 가지고 있습니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get nests list
- url: `/nests`
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| app | number | `1` | app srl |
| id | string | `hello` | 고유 id값 |
| name | string | `Hello nest` | filename |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |


## get nest
- url: `/nests/[n]` (n:srl) or `/nests/id/[s]` (s:id)
- method: GET

사용하는 파라메터 목록.  
`srl`번호로 사용할 수 있지만 `id` 이름으로 사용할 수 있다는것을 참고해주세요.

| name | type | example | description |
|:---:|:---:|---|---|
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |


## add nest
- url: `/nests`
- method: POST

사용하는 body 항목

| key | type | example | required | description |
|:---:|:---:|---|:---:|---|
| app_srl | number | `1` | true | app srl |
| id | string | `hello` | true | nest id |
| name | string | `hello app` | true | name |
| description | string | `memo` | false | description |
| json | string | `{"foo": "bar"}` | false | json data |


## edit nest
- url: `/nests/[n]/edit` (n:srl)
- method: POST

사용하는 body 항목

| key | type | example | required | description |
|:---:|:---:|---|:---:|---|
| app_srl | number | `1` | true | app srl |
| id | string | `hello` | true | nest id |
| name | string | `hello app` | true | name |
| description | string | `memo` | false | description |
| json | string | `{"foo": "bar"}` | false | json data |


## delete nest
- url: `/nests/[n]/delete` (n:srl)
- method: POST