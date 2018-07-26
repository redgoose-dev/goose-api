# module controller

각 모듈의 요청과 응답에 대한 컨트롤러에 대해서 자세하게 다룹니다.  
일부 값들은 공통적으로 사용되는것을 참고해주세요. 해당 부분에 대한 자세한 내용은 [Reference](#reference) 섹션을 참고해 주세요.

<p><br/></p>


## apps

`nest`를 그룹짓는 용도로 사용합니다. 주로 한 프로젝트의 그루핑을 위한 목록이라고 볼 수 있습니다.  
하나의 프로젝트를 `nest`와 `article`의 그룹이 되는 최상위 부모 역할을 할 수 있습니다.

### get apps list
- url: `/apps`
- method: GET
- token level: public

다음은 `apps`에서 사용하는 파라메터 목록입니다.

| name | type | example | description |
|:---:|:---:|---|---|
| id | string | `goose_app` | id |
| name | string | `Goose` | name |
| description | string | `app description` | description |

### get app
- url: `/apps/[n]` (n:srl)
- method: GET
- token level: public

### add app
- url: `/apps`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| id | string | `goose_app` | app 아이디. 중복복된 아이디를 넣을 수 없습니다. |
| name | string | `Goose's app` | app 이름 |
| description | string | `app description` | app description |

### edit app
- url: `/apps/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| id | string | `goose_app` | app 아이디. 중복복된 아이디를 넣을 수 없습니다. |
| name | string | `Goose's app` | app 이름 |
| description | string | `app description` | app description |

### delete app
- url: `/apps/[n]/delete` (n:srl)
- method: POST
- token level: admin

<p><br/></p>


## articles

가장 기초적인 요소이며 컨텐츠가 되는 서비스입니다.

### get articles list
- url: `/articles`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| app | number | `1` | app srl |
| nest | number | `1` | nest srl |
| category | number | `1` | category srl |
| user | number | `1` | user srl |
| title | string | `toy` | 제목 검색어 |
| content | string | `boy` | 본문내욕 검색어 |

### get article
- url: `/articles/[n]` (n:srl)
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| hit | number | `0,1` | 이 항목을 `1`로 넣어서 사용하면 응답을 받을때 조회수가 올라갑니다. |

### add article
- url: `/articles`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl 번호 |
| nest_srl | number | `1` | nest srl 번호 |
| category_srl | number | `1` | category srl 번호 |
| user_srl | number | `1` | user srl 번호 |
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| json | string | `{"foo", "bar"}` | 글 본문 |

### edit article
- url: `/articles/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl 번호 |
| nest_srl | number | `1` | nest srl 번호 |
| category_srl | number | `1` | category srl 번호 |
| user_srl | number | `1` | user srl 번호 |
| title | string | `title name` | 글 제목 |
| content | string | `content body text` | 글 본문 |
| json | string | `{"foo", "bar"}` | 글 본문 |

### delete article
- url: `/articles/[n]/delete` (n:srl)
- method: POST
- token level: admin

<p><br/></p>


## categories

article에 대한 분류로 사용합니다. 가장 작은 단위로 그루핑을 할 수 있습니다.

### get categories list
- url: `/categories`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| nest | number | `1` | nest srl |
| name | string | `name` | 카테고리 이름 |

`order=turn`를 활용하여 직접 변경한 순서대로 출렬할 수 있습니다.

### get category
- url: `/categories/[n]` (n:srl)
- method: GET
- token level: public

### add category
- url: `/categories/[n]` (n:srl)
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| nest_srl | number | `1` | nest srl 번호 |
| name | string | `title name` | 분류 이름 |

### edit category
- url: `/categories/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| nest_srl | number | `1` | nest srl 번호 |
| name | string | `title name` | 분류 이름 |

### delete category
- url: `/categories/[n]/delete` (n:srl)
- method: POST
- token level: admin

### sort categories
- url: `/categories/sort`
- method: POST
- token level: admin

분류를 새로 정렬할때 사용합니다. 정렬할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| nest_srl | number | `1` | nest srl 번호 |
| srls | string | `3,1,2` | 새로 정렬할 srl 번호들 |

<p><br/></p>


## files

파일들을 서버에서 불러오거나 저장합니다.

### get files list
- url: `/files`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| article | number | `1` | article srl |
| name | string | `filename` | filename |
| type | string | `png` | file type |
| ready | string | `true,false` | 대기상태 |

### get file
- url: `/files/[n]` (n:srl)
- method: GET
- token level: public

### add file
- url: `/files`
- method: POST
- token level: admin

서버에 파일을 업로드하고 데이터베이스에 등록합니다.  
전송할때 `multipart`형식으로 전송합니다.

| key | type | example | description |
|:---:|:---:|---|---|
| article_srls | number | `1` | `article srl` 값입니다. 파일을 여러개 올리면 그 갯수만큼 여러개 지정해줄 수 있습니다. ex) 1,2,3 |
| ready | string | `0,1` | 대기상태 |
| files | File |  | 업로드 파일 |

### edit file
- url: `/files/[n]/edit` (n:srl)
- method: POST
- token level: admin

서버에 파일을 삭제하고 다시 업로드하고나서 데이터베이스에서 수정합니다.  
전송할때 `multipart`형식으로 전송합니다.

| key | type | example | description |
|:---:|:---:|---|---|
| article_srl | number | `1` | `article srl` 값입니다. |
| ready | string | `0,1` | 대기상태 |
| files | File |  | 업로드 파일 |

### delete file
- url: `/files/[n]/delete` (n:srl)
- method: POST
- token level: admin

### upload file
- url: `/files/upload-file`
- method: POST
- token level: admin

데이터베이스에 등록하지 않고 서버에 파일을 업로드할 수 있습니다.

| key | type | example | description |
|:---:|:---:|---|---|
| sub_dir | string | `thumbnail` | 서브 디렉토리 이름 |
| file | File,string | | 업로드할 파일입니다. multipart에서 File로 넣거나 base64 문자열로 넣어서 업로드할 수 있습니다. |

### remove file
- url: `/files/remove-file`
- method: POST
- token level: admin

데이터베이스에 등록하지 않고 서버에 파일을 삭제합니다.

| key | type | example | description |
|:---:|:---:|---|---|
| path | string | `filename.jpg` | 삭제할 파일 경로를 입력해줍니다. 프로젝트 루트경로 기준으로 입력합니다. |

<p><br/></p>


## json

다목적으로 사용하기위한 데이터 트리를 관리하는 모듈입니다.

### get json list
- url: `/json`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| name | string | `foo` | filename |

### get json
- url: `/json/[n]` (n:srl)
- method: GET
- token level: public

### add json
- url: `/json`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| name | string | `name` | json의 이름 |
| json | string | `{"foo": "bar"}` | json 데이터 |

### edit json
- url: `/json/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| name | string | `name` | json의 이름 |
| json | string | `{"foo": "bar"}` | json 데이터 |

### delete json
- url: `/json/[n]/delete` (n:srl)
- method: POST
- token level: admin

<p><br/></p>


## nests

article 들을 그루핑하는 역할을 합니다. category도 같은 기능을 가지고 있지만 nest는 더 넓게 그루핑을 하거나 더 많은 기능을 가지고 있습니다.

### get nests list
- url: `/nests`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| app | number | `1` | app srl |
| id | string | `hello` | 고유 id값 |
| name | string | `Hello nest` | filename |

### get nest
- url: `/nests/[n]` (n:srl)
- method: GET
- token level: public

### add nest
- url: `/nests`
- method: POST
- token level: admin

데이터를 추가할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl |
| id | string | `hello` | nest id |
| name | string | `hello app` | name |
| description | string | `memo` | description |
| json | string | `{"foo": "bar"}` | json data |

### edit nest
- url: `/nests/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| app_srl | number | `1` | app srl |
| id | string | `hello` | nest id |
| name | string | `hello app` | name |
| description | string | `memo` | description |
| json | string | `{"foo": "bar"}` | json data |

### delete nest
- url: `/nests/[n]/delete` (n:srl)
- method: POST
- token level: admin

<p><br/></p>


## users

사용자 데이터를 관리하며 사용자를 추가하거나 정보를 관리합니다.

### get users list
- url: `/users`
- method: GET
- token level: public

다음은 이 요청에서 사용하는 파라메터 목록

| name | type | example | description |
|:---:|:---:|---|---|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `foo` | 이름 |
| level | number | `100` | 유저 레벨 |

### get user
- url: `/users/[n]` (n:srl)
- method: GET
- token level: public

### add user
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

### edit user
- url: `/nests/[n]/edit` (n:srl)
- method: POST
- token level: admin

데이터를 수정할때 사용하는 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| email | string | `abc@abc.com` | 이메일 주소 |
| name | string | `name` | name |
| level | number | `{"foo": "bar"}` | 유저 레벨. 설정된 관리자 레벨보다 낮으면 일부 기능을 사용할 수 없습니다. |

### delete user
- url: `/users/[n]/delete` (n:srl)
- method: POST
- token level: admin

### change password
- url: `/users/[n]/change-password` (n:srl)
- method: POST
- token level: admin

비밀번호를 변경합니다. 거기에 필요한 body 항목입니다.

| key | type | example | description |
|:---:|:---:|---|---|
| pw | string | `foo` | 현재 패스워드 |
| new_pw | string | `bar` | 새로운 패스워드 |
| confirm_pw | string | `bar` | 새로운 패스워드 확인 |

<p><br/></p>


## authorization

사용자 로그인하거나 로그아웃, 토큰에 관련된 기능을 가지고 있습니다.

### login
- url: `/auth/login`
- method: POST
- token level: public

사용자 로그인을 합니다.  
로그인을 성공하면 사용자 정보와 함께 유저용 토큰값을 가져올 수 있습니다.

| key | type | example | description |
|:---:|:---:|---|---|
| email | string | `abc@abc.com` | 이메일 주소 |
| pw | string | `bar` | 패스워드 |
| host | string | `localhost:3000` | 클라이언트 호스트 주소 |

### logout
- url: `/auth/logout`
- method: POST
- token level: user

로그아웃 합니다.  
만료된 토큰이 아니라면 블랙리스트에 토큰을 등록합니다.

<p><br/></p>


## token

### decode
- url: `/token/clear`
- method: POST
- token level: public

토큰속의 `data`필드에 있는 값들을 가져옵니다. 토큰이 어떤값이 들어있는지 확인하기 위하여 사용됩니다.

### clear
- url: `/token/token-clear`
- method: POST
- token level: admin

이것을 요청하면 블랙리스트에 들어가고 만료된 토큰은 삭제하면서 로그아웃하면서 토큰값이 쌓이는것을 정리해줍니다.

<p><br/></p>


## manager

[goose-manager](https://github.com/redgoose-dev/goose-manager)에서 사용하는 컨트롤러들입니다.

<p><br/></p>

----

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

<p><br/></p>

----

<p><br/></p>


## external controller

사용자가 직접 API 컨트롤러를 만들어 작성할 수 있습니다.  
기본적으로 제공되는 서비스가 실제 만드는 서비스에 필요한 기능이 부족할 수 있다는 결론이 나와서 필요한 부분은 직접 개발할 수 있도록 공간을 마련해주는것이 좋겠다 생각으로 만들어 졌습니다.

### filename, url
파일이름은 `{METHOD}_{FILENAME}.php` 형식으로 만들어주시길 바랍니다.  
그리고 접근할 수 있는 주소는 `/external/{METHOD}_{FILENAME}` 형식으로 접속할 수 있습니다.

| method | filename | url |
|:---:|---|---|
| GET | `get_filename.php` | `/external/filename` |
| POST | `post_filename.php` | `/external/filename` |

메서드는 `GET`과 `POST`만 사용할 수 있습니다.
