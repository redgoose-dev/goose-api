# routes

[Best Practice](https://elysiajs.com/essential/best-practice.html) 문서에서 구조를 참고하고 구성합니다.

## Endpoints

- `/`
- `/app`
- `/article`
- `/file`

## Service 클래스 타입 적용

```typescript
import Service from '@/classes/Service'

export default async (ctx) => {
  const { service } = ctx.store as { service: Service }

  return {
    message: `Hello! ${service.serviceName}`,
  }
}
```

## 라우트 주석 가이드

`./app/index.ts` 파일에서 라우트 핸들러 주석을 구분하는 이모지입니다.

- 🌿: 목록
- 🌻: 상세데이터
- 🌱: 만들기
- 🌳: 업데이트
- 🍄: 삭제
- 🌵: 처리
- 🍁: 웹소켓
