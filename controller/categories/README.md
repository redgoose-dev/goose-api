# Controller / categories

분류로 사용합니다. 가장 작은 단위로 그루핑을 할 수 있습니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get categories list

- url: `/categories/`
- method: GET

사용하는 파라메터 목록

|     name     |  type  | example         | description                                  |
|:------------:|:------:|-----------------|----------------------------------------------|
|    module    | string | `article,json`  | module                                       |
|    target    | number | `1`             | target srl                                   |
|     name     | string | `name`          | 카테고리 이름                                      |
|  ext_field   | string | `count_article` | 확장 필드                                        |
|    strict    | number | `0,1`           | 일반 유저라면 자신만의 데이터를 가져옵니다.                     |
| visible_type | string | `all`           | 출력 타입을 정합니다. `ext_field=count`값을 사용할때 이용됩니다. |

`order=turn`를 활용하여 직접 변경한 순서대로 출력할 수 있습니다.

### ext_field
- `count`: 분류에 해당되는 articles,json 아이템 갯수
- `all`: 모든 article,json 갯수가 들어있는 항목
- `none`: 분류에 해당안되는 항목


## get category

- url: `/categories/[n]/` (n:srl)
- method: GET

사용하는 파라메터 목록

|     name     |  type  | example | description                                  |
|:------------:|:------:|---------|----------------------------------------------|
|  ext_field   | string | `count` | 확장 필드                                        |
|    strict    | number | `0,1`   | 일반 유저라면 자신만의 데이터를 가져옵니다.                     |
| visible_type | string | `all`   | 출력 타입을 정합니다. `ext_field=count`값을 사용할때 이용됩니다. |

### ext_field
- `count`: 분류에 해당되는 articles 아이템 갯수


## add category

- url: `/categories/[n]/` (n:srl)
- method: POST

데이터를 추가할때 사용하는 body 항목

|    key     |  type  | example        | description   |
|:----------:|:------:|----------------|---------------|
|   module   | string | `article,json` | module        |
| target_srl | number | `1`            | target srl 번호 |
|    name    | string | `title name`   | 분류 이름         |


## edit category

- url: `/categories/[n]/edit/` (n:srl)
- method: POST

데이터를 추가할때 사용하는 body 항목

|    key     |  type  | example        | description   |
|:----------:|:------:|----------------|---------------|
|   module   | string | `article,json` | module        |
| target_srl | number | `1`            | target srl 번호 |
|    name    | string | `title name`   | 분류 이름         |


## delete category

- url: `/categories/[n]/delete/` (n:srl)
- method: POST

분류를 삭제합니다.


## sort categories

- url: `/categories/sort/`
- method: POST

분류를 새로 정렬할때 사용합니다. 정렬할때 사용하는 body 항목입니다.  
turn 번호의 시작은 `1`입니다.

|    key     |  type  | example | description    |
|:----------:|:------:|---------|----------------|
| target_srl | number | `1`     | target srl 번호  |
|    srls    | string | `3,1,2` | 새로 정렬할 srl 번호들 |
