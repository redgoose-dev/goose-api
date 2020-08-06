# Controller / users

사용자 데이터를 관리하며 사용자를 추가하거나 정보를 관리합니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get users list

- url: `/users/`
- method: GET

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:----:|:----:|---------|-------------|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `foo` | 이름 |
| admin | number | `2` | 관리자 유무 |


## get user

- url: `/users/[n]/` (n:srl)
- method: GET


## add user

- url: `/users/`
- method: POST

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:----:|---------|-------------|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `name` | name |
| password | string | `1234` | 비밀번호 |
| password2 | string | `1234` | 비밀번호 확인 |
| admin | number | `2` | 관리자 유무 |


## edit user

- url: `/nests/[n]/edit/` (n:srl)
- method: POST

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:----:|---------|-------------|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `name` | name |
| admin | number | `2` | 관리자 유무 |


## delete user

- url: `/users/[n]/delete/` (n:srl)
- method: POST


## change password

- url: `/users/[n]/change-password/` (n:srl)
- method: POST

비밀번호를 변경합니다. 다음은 필요한 body 항목입니다.  
기존 패스워드를 맞춰야지 변경할 수 있음을 주의해주세요. (관리자라도 기존 패스워드를 모르면 변경할 수 없습니다.)

| key | type | example | description |
|:---:|:----:|---------|-------------|
| password | string | `foo` | 현재 패스워드 |
| new_password | string | `bar` | 새로운 패스워드 |
| confirm_password | string | `bar` | 새로운 패스워드 확인 |
