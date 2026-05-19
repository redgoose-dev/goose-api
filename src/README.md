# Project main

## CLI 명령어

다음과 같은 구조로 실행합니다.

```shell
bun run dev:util [METHOD] [OPTIONS]
```

### 인스톨

프로그램에 필요한 데이터와 데이터베이스를 만듭니다.

```shell
# 진행에 대한 확인을 하며 초기 계정 정보를 입력해야 합니다.
bun run dev:util install

# 실행하면 모든 과정을 자동으로 진행합니다.
bun run dev:util install --id goose --name GOOSE --password 1234
```

### 언인스톨

데이터와 데이터베이스 전부 삭제합니다.

```shell
# 진행에 대한 확인을 합니다.
bun run dev:util uninstall

# 확인없이 바로 언인스톨을 진행합니다.
bun run dev:util uninstall -y
```

### 비밀번호 재설정

비밀번호 프로바이더에서 사용하는 비밀번호를 재설정합니다.

```shell
bun run dev:util reset-password
```
