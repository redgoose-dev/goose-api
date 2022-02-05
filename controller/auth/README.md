# Controller / authorization

사용자 로그인하거나 로그아웃, 토큰에 관련된 기능을 가지고 있습니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## login

- url: `/auth/login/`
- method: POST

사용자 로그인을 합니다.  
로그인을 성공하면 사용자 정보와 함께 유저용 토큰값을 가져올 수 있습니다.

| key | type | example | description |
|:---:|:----:|---------|-------------|
| email | string | `address@domain.com` | 이메일 주소 |
| password | string | `password` | 패스워드 |


## logout

- url: `/auth/logout/`
- method: POST

로그아웃 합니다.  
만료된 토큰이 아니라면 블랙리스트에 토큰을 등록합니다.
