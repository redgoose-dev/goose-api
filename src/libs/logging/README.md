# logging

로깅도구인 [Logixlysia](https://github.com/PunGrumpy/logixlysia) 를 사용하여 로그를 기록하는 모듈입니다.

> website: https://logixlysia.vercel.app/


## 라우터 핸들러에서 사용하기

핸들러 함수에서 `store.logger` 객체를 사용하여 로그를 기록할 수 있습니다.
`logger.info()` 메서드를 호출할때마다 로그가 기록됩니다.

```typescript
export default async (ctx) => {
  const { request, store } = ctx
  store.logger.info(request, 'Hello, World!', {
    foo: 'bar',
  })
  return {
    message: 'Hello',
  }
}
```


## SQLite 로그 기록

`LOG_RECORD=true`이면 `PATH_DATA/log.sqlite`에 로그를 기록합니다.
SQLite 파일은 첫 로그를 기록할 때 자동으로 생성합니다.

로그 기록은 요청마다 즉시 커밋하지 않고 짧은 대기열에 모아 하나의 트랜잭션으로
기록합니다. 서비스 데이터베이스와 연결을 공유하지 않으며 WAL 모드를 사용하므로,
별도 연결을 사용하는 로그 조회가 기록 트랜잭션을 기다리는 시간을 줄일 수 있습니다.

기록 대기열 정책은 `src/libs/assets.ts`의 `LOG_RECORD_DB_POLICY`에서 관리합니다.
`LOG_RECORD_RETENTION_DAYS`만 환경변수로 관리하며 SQLite 로그의 보관 기간을 지정합니다.
