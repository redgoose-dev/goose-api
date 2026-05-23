# file

## TODO

### 동적으로 외부 라이브러리 불러오기

라이브러리를 정적으로 불러오는 부분에 대하여 AI랑 이야기를 하다가 라우터는 정적으로 불러오고 라이브러리는 동적으로 불러오는 방법이 좋겠다는 생각이 들었다.

```typescript
export default async (ctx) => {
  const sharp = (await import('sharp')).default

  // sharp 사용
  return { ok: true }
}
```
