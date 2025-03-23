# Authorization

인증에 대한 기능들을 가지고 있는 엔드포인트입니다.

## 프로바이더 종류

- `discord`: 디스코드 (https://discord.com/developers/applications)
- `password`: ID/PW 인증 (OAuth 없이 사용합니다.)

## 인증 프로바이더 추가하기

### 리다이렉트로 OAuth 추가하기

OAuth 인증을 추가하기 위해서는 리다이렉트 URL을 설정해야 합니다. 이 URL은 사용자가 인증을 완료한 후에 돌아오는 URL입니다.
인증 과정은 다음과 같습니다.


1. `[CLIENT]` 인증시작
2. `[API]` 리다이렉트
3. `[OAuth Service]` 인증 확인
4. `[API]` 콜백
5. `[CLIENT]` 인증완료

다음 경로로 이동하여 인증을 시작합니다.

```
GET /auth/redirect/{PROVIDER}/?redirect_uri={CLIENT_REDIRECT_URI}
```

- `{PROVIDER}`: OAuth 프로바이더 이름. `## 프로바이더 종류` 섹션을 참고하세요.
- `{CLIENT_REDIRECT_URI}`: 인증을 끝내고 돌아올 URL 주소입니다. 클라이언트 서비스에서 엑세스 토큰을 받고 처리하는 주소로 사용하면 됩니다.

### 웹소켓으로 OAuth 추가하기

API 서비스에서 페이지 리로드없이 비동기로 인증하기 위하여 웹소켓을 이용합니다.
클라이언트에서 인증을 시작할때 웹소켓을 연결하고, 상황에 따라 메시지를 주고 받습니다.

테스트 html 코드는 `./get_test_websocket.html` 파일을 참고하거나 개발모드에서 `/auth/test_websocket/` 주소를 열어서 확인할 수 있습니다.

### ID/PW로 인증 추가하기

OAuth 없이 ID/PW로 인증을 추가하기 위해서는 다음과 같은 과정을 거칩니다.

요청은 다음과 같이 합니다.

```
PUT /auth/
{
    "code": "password",
    "id": "{USER_ID}",
    "name": "{USER_NAME}",
    "avatar": "{USER_AVATAR}"
    "email": "{USER_EMAIL}"
    "password": "{USER_PASSWORD}"
}
```

## 인증 목록 가져오기

## 인증 수정하기

## 인증 제거하기
