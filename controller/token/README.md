# Controller / token

`API`에서 반드시 필요한 토큰에 대한 도구들입니다.


## decode

- url: `/token/decode/`
- method: POST

토큰속의 `data`필드에 있는 값들을 가져옵니다. 토큰이 어떤값이 들어있는지 확인하기 위하여 사용됩니다.


## clear

- url: `/token/clear/`
- method: POST

이것을 요청하면 블랙리스트에 들어가고 만료된 토큰은 삭제하면서 로그아웃하면서 토큰값이 쌓이는것을 정리해줍니다.