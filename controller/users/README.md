# Controller / users

사용자 데이터를 관리하며 사용자를 추가하거나 정보를 관리합니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.

## get users list
- url: `/users`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `foo` | 이름 |
| level | number | `1` | 유저 레벨 |

## get user
- url: `/users/[n]` (n:srl)
- method: GET
- token level: public

## add user
- url: `/users`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `name` | name |
| pw | string | `1234` | 비밀번호 |
| pw2 | string | `1234` | 비밀번호 확인 |
| level | number | `{"foo": "bar"}` | 유저 레벨. 설정된 관리자 레벨보다 낮으면 일부 기능을 사용할 수 없습니다. |

## edit user
- url: `/nests/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `name` | name |
| level | number | `{"foo": "bar"}` | 유저 레벨. 설정된 관리자 레벨보다 낮으면 일부 기능을 사용할 수 없습니다. |

## delete user
- url: `/users/[n]/delete` (n:srl)
- method: POST
- token level: admin

## change password
- url: `/users/[n]/change-password` (n:srl)
- method: POST
- token level: admin

비밀번호를 변경합니다. 거기에 필요한 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| pw | string | `foo` | 현재 패스워드 |
| new_pw | string | `bar` | 새로운 패스워드 |
| confirm_pw | string | `bar` | 새로운 패스워드 확인 |