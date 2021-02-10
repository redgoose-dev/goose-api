# Controller / checklist

체크리스트 관리도구로 사용합니다.

## get checklist list

- url: `/checklist/`
- method: GET

체크리스트 아이템들 목록을 가져옵니다.


## get checklist

- url: `/checklist/[n]/` (n:srl)
- method: GET

체크리스트 아이템 하나를 가져옵니다.


## add checklist

- url: `/checklist/[n]/` (n:srl)
- method: POST

체크리스트를 추가합니다.


## edit checklist

- url: `/checklist/[n]/edit/` (n:srl)
- method: POST

체크리스트를 수정합니다.


## delete checklist

- url: `/checklist/[n]/delete/` (n:srl)
- method: POST

체크리스트를 삭제합니다.
