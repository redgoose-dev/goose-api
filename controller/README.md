# service controller

각 서비스의 요청과 응답에 대한 컨트롤러에 대해서 자세하게 다룹니다.  
일부 값들은 공통적으로 사용되는것을 참고해주세요. 해당 부분에 대한 자세한 내용은 [Reference](#reference) 섹션을 참고해 주세요.

다음 링크는 각 서비스의 자세한 파라메터와 설명들에 대한 가이드를 참고할 수 있습니다.

- [./apps](https://github.com/redgoose-dev/goose-api/tree/master/controller/apps)
- [./articles](https://github.com/redgoose-dev/goose-api/tree/master/controller/articles)
- [./auth](https://github.com/redgoose-dev/goose-api/tree/master/controller/auth)
- [./categories](https://github.com/redgoose-dev/goose-api/tree/master/controller/categories)
- [./files](https://github.com/redgoose-dev/goose-api/tree/master/controller/files)
- [./json](https://github.com/redgoose-dev/goose-api/tree/master/controller/json)
- [./nests](https://github.com/redgoose-dev/goose-api/tree/master/controller/nests)
- [./token](https://github.com/redgoose-dev/goose-api/tree/master/controller/token)
- [./users](https://github.com/redgoose-dev/goose-api/tree/master/controller/users)
- [./external](https://github.com/redgoose-dev/goose-api/tree/master/controller/external)
- [./manager](https://github.com/redgoose-dev/goose-api/tree/master/controller/manager)

<p><br/></p>

## Reference

### headers
API를 사용하기 위하여 반드시 headers에서 토큰값을 넣어줘야합니다.  
그에관한 자세한 내용은 [링크]를 참고해주세요.

### global parameter
데이터를 가져올때 기본적으로 사용하는 파라메터 입니다.

다음은 데이터 __리스트 형태__를 불러올때 공통적으로 사용하는 파라메터입니다.

| name | type | example | description |
|:---:|:---:|---|---|
| field | string | `srl,title` | 가져오려는 필드이름 |
| limit | string | `0,20` | 출력 갯수를 잘라냅니다. `20`형태로 넣으면 20개를 가져오고, `2,10`으로 넣으면 2번째부터 10번째까지 가져옵니다. |
| unlimit | number | `0,1` | 제한없이 글을 불러오려면 값을 넣어줍니다. |
| page | number | `1` | page number |
| size | number | `20` | 한페이지에 가져오는 글 갯수 |
| sort | string | `desc,asc` | 역순으로 정렬 |
| order | string | `srl` | 정렬하는 기준이 되는 필드 |
| field | string | `srl,title` | 가져오려는 필드 |
| min | number | `0,1` | 결과값을 압축 |

다음은 __하나를 선택한 데이터__에서 공통적으로 사용하는 파라메터입니다.

| name | type | example | description |
|:---:|:---:|---|---|
| field | string | `srl,title` | 가져오려는 필드이름 |
| min | number | `0,1` | 결과값을 압축 |

### global response
다음은 모든 응답이 기본적으로 출력되는 값입니다.

| name | type | example | debug | description |
|:---:|:---:|---|:---:|---|
| code | int | `200` | o | 결과 코드 |
| query | string | `insert ...` | o | db query |
| success | boolean | `true` | x | 정상적으로 처리되었는지에 대한 여부 |
| _token | string | `eyJ0e...` | x | 사용자 토큰이 만료되면 재발행되는 토큰값입니다. |
| time | string | `4.562ms` | o | 사용자 토큰이 만료되면 재발행되는 토큰값입니다. |
| message | string | `message..` | x | 처리가 실패했을때의 이유를 표시합니다. |
| data | object | `{}` | x | 처리가 성공했을때 결과 데이터 |

다음은 데이터를 추가하면 기본적으로 출력되는 응답입니다.

| name | type | example | description |
|:---:|:---:|---|---|
| srl | int | `1` | 추가된 데이터의 `srl`값 |