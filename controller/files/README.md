# Controller / files

파일들을 서버에서 불러오거나 저장합니다.

공통되는 요소는 [Reference](https://github.com/redgoose-dev/goose-api/tree/master/controller#reference) 섹션을 참고해주세요.


## get files

- url: `/files/`
- method: GET

사용하는 파라메터 목록

| name   | type   | example    | description                |
|:-------|:-------|:-----------|:---------------------------|
| target | number | `1`        | 모듈(articles,comments)의 srl |
| name   | string | `filename` | filename                   |
| type   | string | `png`      | file type                  |
| module | string | `articles` | module name                |
| strict | number | `0,1`      | 일반 유저라면 자신만의 데이터를 가져옵니다.   |


## get file

- url: `/files/[n]/` (n:srl)
- method: GET

사용하는 파라메터 목록

| name   | type   | example | description              |
|:-------|:-------|:--------|:-------------------------|
| strict | number | `0,1`   | 일반 유저라면 자신만의 데이터를 가져옵니다. |


## add file

- url: `/files/`
- method: POST

서버에 파일을 업로드하고 데이터베이스에 등록합니다.  
전송할때 `multipart`형식으로 전송합니다.

| key        | type   | example    | description      |
|:-----------|:-------|:-----------|:-----------------|
| target_srl | number | `1`        | 모듈들의 `srl` 값입니다. |
| module     | string | `articles` | module name      |
| file       | File   |            | 업로드 파일           |


## edit file

- url: `/files/[n]/edit/` (n:srl)
- method: POST

서버에 파일을 삭제하고 다시 업로드하고나서 데이터베이스에서 수정합니다.  
전송할때 `multipart`형식으로 전송합니다.

| key        | type   | example    | description      |
|:-----------|:-------|:-----------|:-----------------|
| target_srl | number | `1`        | 모듈들의 `srl` 값입니다. |
| module     | string | `articles` | module name      |
| file       | File   |            | 업로드 파일           |


## get files in directory

디렉토리속에 파일들의 목록을 가져옵니다.

- url: `/files/get-files/[dir]/` (dir: directory name)
- method: GET


## delete file

- url: `/files/[n]/delete/` (n:srl)
- method: POST


## upload file

- url: `/files/upload-file/`
- method: POST

데이터베이스에 등록하지 않고 서버에 파일을 업로드할 수 있습니다.

| key    | type   | example  | description             |
|:-------|:-------|:---------|:------------------------|
| dir    | string | `assets` | 서브 디렉토리 이름              |
| file   | File   |          | `File`형식의 업로드할 파일입니다.   |
| base64 | string |          | `base64`형식의 업로드할 파일입니다. |


## remove file

- url: `/files/remove-file/`
- method: POST

데이터베이스에 등록하지 않고 서버에 파일을 삭제합니다.

| key  | type   | example        | description                              |
|:-----|:-------|:---------------|:-----------------------------------------|
| dir  | string | `assets`       | 서브 디렉토리 이름                               |
| path | string | `filename.jpg` | 삭제할 파일 경로를 입력해줍니다. 프로젝트 루트경로 기준으로 입력합니다. |


## resize image

- url: `/files/resize/`
- method: GET

서비스에 업로드된 첨부 이미지를 리사이즈된 형태로 불러옵니다.
처음 요청하면 리사이즈후에 캐시영역에 저장하고 그 이후에 호출하면 리사이즈된 이미지로 불러옵니다.

| key     | type   | example         | description                          |
|:--------|:-------|:----------------|:-------------------------------------|
| path    | string | `filename.jpg`  | 리사이즈 경로. `/data/upload` 이후부터 사용      |
| type    | string | `cover`         | 리사이즈 방식 (cover: 크롭, null: 비율대로 리사이즈) |
| width   | int    | `640`           | 가로사이즈                                |
| height  | int    | `480`           | 세로사이즈                                |
| quality | int    | `90`            | 이미지 퀄리티                              |

