# Controller / checklist

체크리스트 관리도구로 사용합니다.  
이 컨텐츠는 자신만의 데이터로만 컨트롤할 수 있습니다.

## get checklist list

- url: `/checklist/`
- method: GET

체크리스트 아이템들 목록을 가져옵니다.

| name | type | example | description |
|:----:|:----:|---------|-------------|
| start | date | `0000-00-00` | 날짜범위 시작 |
| end | date | `0000-00-00` | 날짜범위 끝 |
| q | string | `toy` | 본문내용 키워드 검색 |


## get checklist

- url: `/checklist/[n]/` (n:srl)
- method: GET

체크리스트 아이템 하나를 가져옵니다.


## add checklist

- url: `/checklist/[n]/` (n:srl)
- method: POST

체크리스트를 추가합니다.

|   key   | method |    type | example | description |
|:-------:|:------:|--------:|---------|-------------|
| content | POST | string | `- [ ] check item` | 글 본문 |
| regdate | POST | date | `0000-00-00` | 등록날짜이며 중복된 날짜의 아이템은 등록할 수 없습니다. 이 필드는 사용을 권장하지 않습니다. |
| return | GET | boolean | 1 | 추가한 데이터를 되돌려 받는다. |


## edit checklist

- url: `/checklist/[n]/edit/` (n:srl)
- method: POST

체크리스트를 수정합니다.

| key | type | example | description |
|:---:|:----:|---------|-------------|
| content | string | `- [ ] check item` | 글 본문 |


## delete checklist

- url: `/checklist/[n]/delete/` (n:srl)
- method: POST

체크리스트를 삭제합니다.
