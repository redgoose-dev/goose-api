# Controller / apps

`nest`를 그룹짓는 용도로 사용합니다. 주로 한 프로젝트의 그루핑을 위한 목록이라고 볼 수 있습니다.  
하나의 프로젝트를 `nest`와 `article`의 그룹이 되는 최상위 부모 역할을 할 수 있습니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get apps list
- url: `/apps/`
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| id   | string | `goose_app` | id |
| name | string | `Goose` | name |
| description | string | `app description` | description |
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |


## get app
- url: `/apps/[n]/` (n:srl)
- method: GET

사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| strict | number | `0,1` | 일반 유저라면 자신만의 데이터를 가져옵니다. |


## add app
- url: `/apps/`
- method: POST

데이터를 추가할때 사용하는 body 항목

| key | type | example | description |
|:---:|:----:|---------|-------------|
| id | string | `goose_app` | app 아이디. 중복복된 아이디를 넣을 수 없습니다. |
| name | string | `Goose's app` | app 이름 |
| description | string | `app description` | app description |


## edit app
- url: `/apps/[n]/edit/` (n:srl)
- method: POST

데이터를 수정할때 사용하는 body 항목

| key | type | example | description |
|:---:|:----:|---------|-------------|
| id | string | `goose_app` | app 아이디. 중복복된 아이디를 넣을 수 없습니다. |
| name | string | `Goose's app` | app 이름 |
| description | string | `app description` | app description |


## delete app
- url: `/apps/[n]/delete/` (n:srl)
- method: POST

데이터를 수정할때 사용하는 body 항목

| key | type | example | description |
|:---:|:----:|---------|-------------|
| remove_children | number | `0,1` | 하위 데이터들(nests,articles,categories,files)을 삭제할지에 대한 여부 |
