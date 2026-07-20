# goose-api

redgoose 컨텐츠 API 프로젝트
이 API 프로그램은 [Goose](https://github.com/redgoose-dev/goose)에서 시작된 개인용 CMS 중 하나의 프로젝트입니다.

## CLI 명령어

개발 환경에서는 다음 형식으로 유틸리티 명령을 실행합니다.

```shell
bun run dev:util [METHOD] [OPTIONS]
```

### 캐시 정리

캐시 정리 명령은 변환된 이미지 캐시와 이전 형식의 쿼리별 JSON 캐시를 정리합니다. 코드별 기본 메타데이터 JSON은 삭제하지 않습니다.

기본 실행은 삭제하지 않고 대상만 확인하는 `dry-run`입니다.

```shell
# 개발 환경: 삭제 대상 확인
bun run dev:util clean-cache --days 30 --dry-run

# 개발 환경: 실제 삭제
bun run dev:util clean-cache --days 30 --execute
```

옵션 설명:

- `--days`: 변환 캐시의 보관 기간입니다. 기본값은 30일입니다.
- `--dry-run`: 삭제하지 않고 대상 파일만 출력합니다.
- `--execute`: 확인된 대상 파일을 실제로 삭제합니다.

Docker Compose 환경에서는 API와 동일한 이미지·데이터 볼륨을 사용하는 maintenance 컨테이너를 수동 실행합니다.

```shell
# 삭제 대상 확인
docker compose --profile maintenance run --rm --no-deps \
  goose-api-cache-cleanup \
  bun run prod:util clean-cache --days 30 --dry-run

# 실제 삭제
docker compose --profile maintenance run --rm --no-deps \
  goose-api-cache-cleanup
```

캐시 정리는 서버 시작이나 일반적인 `docker compose up` 때 자동 실행되지 않습니다. 필요할 때 위 명령을 직접 실행하면 됩니다.
