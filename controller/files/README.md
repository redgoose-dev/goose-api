# Controller / files

파일들을 서버에서 불러오거나 저장합니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.

## get files list
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

## get file
- url: `/files/[n]` (n:srl)
- method: GET
- token level: public

## add file
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

## edit file
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

## delete file
- url: `/files/[n]/delete` (n:srl)
- method: POST
- token level: admin

## upload file
- url: `/files/upload-file`
- method: POST
- token level: admin

데이터베이스에 등록하지 않고 서버에 파일을 업로드할 수 있습니다.

| key | type | example | description |
|:---:|:---:|---|---|
| sub_dir | string | `thumbnail` | 서브 디렉토리 이름 |
| file | File,string | | 업로드할 파일입니다. multipart에서 File로 넣거나 base64 문자열로 넣어서 업로드할 수 있습니다. |

## remove file
- url: `/files/remove-file`
- method: POST
- token level: admin

데이터베이스에 등록하지 않고 서버에 파일을 삭제합니다.

| key | type | example | description |
|:---:|:---:|---|---|
| path | string | `filename.jpg` | 삭제할 파일 경로를 입력해줍니다. 프로젝트 루트경로 기준으로 입력합니다. |