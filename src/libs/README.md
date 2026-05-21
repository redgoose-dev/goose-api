# libs

## logging.ts

로깅도구인 [Logixlysia](https://github.com/PunGrumpy/logixlysia) 를 사용하여 로그를 기록하는 모듈입니다.

> website: https://logixlysia.vercel.app/

### 라우터 핸들러에서 사용하기

핸들러 함수에서 `store.logger` 객체를 사용하여 로그를 기록할 수 있습니다.
`logger.info()` 메서드를 호출할때마다 로그가 기록됩니다.

```typescript
export default async (ctx: Context) => {
  const { request, store } = ctx
  store.logger.info(request, 'Hello, World!', {
    foo: 'bar',
  })
  return {
    message: 'Hello',
  }
}
```
