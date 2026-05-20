# routes

## Endpoints

- `/`
- `/app`
- `/article`
- `/file`

## Service 클래스 타입 적용

```typescript
import type Service from '@/classes/Service'

export default async (ctx) => {
  const { service } = ctx.store as { service: Service }

  return {
    message: `Hello! ${service.serviceName}`,
  }
}
```
