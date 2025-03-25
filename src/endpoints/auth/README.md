# Authorization

인증에 대한 기능들을 가지고 있는 엔드포인트입니다.

## 프로바이더 종류

- `discord`: 디스코드 (https://discord.com/developers/applications)
- `google`: 구글 (https://console.cloud.google.com/apis/credentials)
- `github`: 깃허브 (https://docs.github.com/ko/apps/oauth-apps/building-oauth-apps/creating-an-oauth-app)
- `password`: ID/PW 인증 (OAuth 없이 사용합니다.)


## 인증 프로바이더 추가하기

### 리다이렉트로 OAuth 추가하기

OAuth 인증을 추가하기 위해서는 리다이렉트 URL을 설정해야 합니다. 이 URL은 사용자가 인증을 완료한 후에 돌아오는 URL입니다.
인증 과정은 다음과 같습니다.

1. CLIENT - 인증시작
2. API - 리다이렉트
3. OAuth Service - 인증 확인
4. API - 콜백
5. CLIENT - 인증완료

다음 경로로 이동하여 인증을 시작합니다.

```
GET /auth/redirect/{PROVIDER}/
query = {
  "redirect_uri": "{CLIENT_REDIRECT_URI}"
}
```

- `{PROVIDER}`: OAuth 프로바이더 이름. `## 프로바이더 종류` 섹션을 참고하세요.
- `{CLIENT_REDIRECT_URI}`: 인증을 끝내고 돌아올 URL 주소입니다. 클라이언트 서비스에서 엑세스 토큰을 받고 처리하는 주소로 사용하면 됩니다.
- OAuth 리다이렉트 주소는 `/auth/callback/{PROVIDER}/`으로 사용합니다.

### 웹소켓으로 OAuth 추가하기

API 서비스에서 페이지 리로드없이 비동기로 인증하기 위하여 웹소켓을 이용합니다.
클라이언트에서 인증을 시작할때 웹소켓을 연결하고, 상황에 따라 메시지를 주고 받습니다.

테스트 html 코드는 `./get_test_websocket.html` 파일을 참고하거나 개발모드에서 `/auth/test_websocket/` 주소를 열어서 확인할 수 있습니다.

### ID/PW로 인증 추가하기

OAuth 없이 ID/PW로 인증을 추가하기 위해서는 다음과 같은 과정을 거칩니다.

요청은 다음과 같이 합니다.

```
PUT /auth/
data = {
  "id": "{USER_ID}",
  "name": "{USER_NAME}",
  "avatar": "{USER_AVATAR}"
  "email": "{USER_EMAIL}"
  "password": "{USER_PASSWORD}"
}
```


## 인증 검사하기

엑세스 토큰이 올바른지 확인하기 위해서는 다음과 같은 요청을 보냅니다.


```
POST /auth/checking/
```


## 패스워드로 로그인하기

패스워드 방식의 프로바이더로 로그인하기 위하여 다음과 같이 요청을 보냅니다.

```
POST /auth/login/
data = {
  "id": "{USER_ID}",
  "password": "{USER_PASSWORD}"
}
```


## 로그아웃

```
POST /auth/logout/
headers = {
  "Authorization": "{ACCESS_TOKEN}"
}
```


## 인증 목록 가져오기

다음과 같은 요청으로 프로바이더 목록을 가져옵니다.

```
GET /auth/
```


## 프로바이더 수정하기

```
PATCH /auth/{SRL}/
```


## 프로바이더, 토큰 제거하기

```
DELETE /auth/{SRL}/
```



## 프로바이더 설정 페이지 링크

- 디스코드: https://discord.com/developers/applications
- 구글: https://console.cloud.google.com/apis/credentials
- 깃허브: https://github.com/settings/apps
