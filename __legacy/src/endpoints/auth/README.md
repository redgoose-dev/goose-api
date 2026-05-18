# auth

인증에 대한 기능들을 가지고 있는 엔드포인트입니다.

## 프로바이더 종류

- `discord`: 디스코드 (https://discord.com/developers/applications)
- `github`: 깃허브 (https://docs.github.com/ko/apps/oauth-apps/building-oauth-apps/creating-an-oauth-app)
- `google`: 구글 (https://console.cloud.google.com/apis/credentials)
- `password`: ID/PW 인증 (OAuth 없이 사용)


## OAuth 인증 서비스

OAuth 인증 서비스를 이용하여 인증하거나 프로바이더 등록할 수 있습니다.
처음 인증하면 프로바이더 등록할 수 있고, 등록되어 있다면 검사만 합니다.

### 리다이렉트로 OAuth 추가하기

OAuth 인증을 추가하기 위해서는 리다이렉트 URL을 설정해야 합니다. 이 URL은 사용자가 인증을 완료한 후에 돌아오는 URL입니다.
인증 과정은 다음과 같습니다.

1. CLIENT - 인증시작
2. API - 인증 서비스로 리다이렉트
3. OAuth Service - 인증 서비스에서 인증 확인
4. API - 인증 서비스에서 API 서비스로 리다이렉트
5. CLIENT - 인증완료

다음 경로로 이동하여 인증을 시작합니다.

```
GET /auth/redirect/{provider:str}/

@param {str} provider / [required] OAuth 프로바이더 이름
@query {str} redirect_uri / [required] 인증을 끝내고 돌아올 URL 주소입니다. 클라이언트 서비스에서 엑세스 토큰을 받고 처리하는 주소로 사용하면 됩니다.
@query {str} token / 엑세스 토큰. 이미 프로바이더가 등록되어 있는 상태라면 인증검사가 꼭 필요합니다.
```

> OAuth 리다이렉트 주소는 `/auth/callback/{provider}/`으로 사용합니다.

### 웹소켓으로 OAuth 추가하기

API 서비스에서 페이지 리로드없이 비동기로 인증하기 위하여 웹소켓을 이용합니다.
클라이언트에서 인증을 시작할때 웹소켓을 연결하고, 상황에 따라 메시지를 주고 받습니다.

테스트 html 코드는 [get_test_websocket.html](./get_test_websocket.html) 파일을 참고하거나 개발서버를 열고 `/auth/test_websocket/` 주소를 이용하여 테스트할 수 있습니다.


## 인증 검사하기

엑세스 토큰이 올바른지 확인하기 위해서는 다음과 같은 요청을 보냅니다.
인증 성공하면 프로바이더 정보를 받을 수 있습니다. 만약 401 에러가 발생하면 상태코드가 202로 응답합니다.

```
POST /auth/checking/

@headers {str} Authorization / 액세스 토큰
```


## 프로바이더

프로바이더 관리에 대한 API 입니다.

### 패스워드 타입의 프로바이더 만들기

OAuth 없이 ID/PW로 인증을 추가하기 위해서 `password` 프로바이더 데이터를 추가합니다.

```
PUT /auth/provider/

@headers {str} Authorization / 액세스 토큰
@data {str} id / [required] 사용자 아이디
@data {str} name / 사용자 이름
@data {str} avatar / 사용자 아바타 URL
@data {str} email / [required] 사용자 이메일
@data {str} password / [required] 사용자 비밀번호
```

### 수정하기

```
PATCH /auth/provider/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 프로바이더 srl 번호
@data {str} id / 사용자 아이디
@data {str} name / 사용자 이름
@data {str} avatar / 사용자 아바타 URL
@data {str} email / 사용자 이메일
@data {str} password / 사용자 비밀번호
```

### 목록 가져오기

```
GET /auth/providers/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} redirect_uri / [required] 인증을 끝내고 돌아올 클라이언트 URL 주소입니다.
```

### 상세정보 가져오기

```
GET /auth/provider/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / 프로바이더 srl 번호
```

### 제거하기

```
DELETE /auth/provider/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@param {int} srl / [required] 프로바이더 srl 번호
```


## 패스워드 타입으로 인증

패스워드 방식의 프로바이더에 관한 API 입니다.

### 로그인 준비

```
POST /auth/ready-login/

@query {str} redirect_uri / [required] 인증을 끝내고 돌아올 클라이언트 URL 주소
```

### 로그인

```
POST /auth/login/

@data {str} id / [required] 사용자 아이디
@data {str} password / [required] 사용자 비밀번호
```

### 로그아웃

```
POST /auth/logout/

@headers {str} Authorization / [required] 액세스 토큰
```


## 엑세스 토큰

엑세스 토큰에 대하여 다룹니다.

### 엑세스 토큰 재발급받기

```
POST /auth/renew/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} provider / [required] 프로바이더 코드
@data {str} refresh / [required] 리프레시 토큰
```

### 공개용 엑세스 토큰

공개 영역에서 사용할 수 있는 엑세스 토큰을 관리합니다.
이 토큰은 인증이 필요하지 않으며 일부 기능만 이용할 수 있습니다.

#### 만들기

```
PUT /auth/token/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} description / 토큰 설명
```

#### 조회

```
GET /auth/token/

@headers {str} Authorization / [required] 액세스 토큰
@query {str} order='srl' / 정렬 기준 필드
@query {str} sort='desc' / 정렬 방식 (asc,desc)
@query {str} token / 토큰
@query {str} mod / 모드 (provider)
```

#### 설명 업데이트

```
PATCH /auth/token/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
@data {str} description / 토큰 설명
```

#### 만료시키기

```
DELETE /auth/token/{srl:int}/

@headers {str} Authorization / [required] 액세스 토큰
```


## 프로바이더 설정 페이지 링크

- 디스코드: https://discord.com/developers/applications
- 구글: https://console.cloud.google.com/apis/credentials
- 깃허브: https://github.com/settings/apps
