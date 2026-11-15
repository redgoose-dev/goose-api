# 코딩 컨벤션

## 규칙의 근거

명시적 설정은 [.editorconfig](../.editorconfig), [tsconfig.json](../tsconfig.json)이다. 아래 문법·구조 관례는 주로 [app 라우트](../src/routes/app/__index.ts), [app 모델](../src/routes/app/__model.ts), [app 생성](../src/routes/app/put-item.ts), [Password](../src/classes/provider/Password.ts)에서 확인했다. 모든 기존 파일이 완벽히 일치하지는 않는다. 우연한 예외를 확대하지 말고 아래 기준과 수정 지점의 문맥을 함께 따른다.

현재 추적 파일에는 ESLint/Prettier 설정과 lint/format 스크립트가 없다. 일반적인 포매터 기본값을 이 저장소의 규칙으로 간주하지 않는다.

## 서식

- 공백 2칸, UTF-8, LF, 파일 끝 개행. 일반 파일은 줄 끝 공백을 제거한다. Markdown은 `.editorconfig`의 trailing whitespace 예외가 있다.
- TypeScript는 세미콜론을 생략하고 일반 문자열에 작은따옴표, 보간 문자열에 백틱을 사용한다.
- 함수 선언·클래스 메서드·여러 줄 제어문의 여는 중괄호는 다음 줄에 둔다. `else`, `catch`도 별도 줄이다.
- 화살표 콜백의 `{`는 같은 줄이다. 클래스 선언과 객체 리터럴의 `{`도 같은 줄이다. 단순 guard는 `if (!value) return`처럼 한 줄을 허용한다.
- 여러 줄 객체·배열·인자 목록은 마지막 쉼표를 기본으로 한다. 객체 구조 분해와 named import에는 내부 공백을 둔다. 짧은 배열의 `[ 'value' ]` 표기는 주변 코드와 맞춘다.
- 논리적인 단계 사이에 빈 줄을 둔다. 코드를 압축하거나 한 줄에 여러 문장을 넣지 않는다. 기존 파일 전체를 재포맷하지 않는다.

```typescript
export function hasValue(value?: string): boolean
{
  if (!value) return false
  if (value.length > 0)
  {
    return true
  }
  else
  {
    return false
  }
}

const values = [ 'first', 'second' ].map((value) => {
  return value.toUpperCase()
})
```

## 이름과 import/export

| 대상 | 관례 |
| --- | --- |
| 기능 폴더 | 단수 소문자: `article`, `provider` |
| 라우트 처리 파일 | 메서드 + 동작의 kebab-case: `get-index.ts`, `put-item.ts`, `patch-change-nest.ts` |
| 공통 역할 파일 | `__index.ts`, `__model.ts`, 필요할 때 `__helper.ts` |
| 클래스 파일/타입 | PascalCase: `ServiceError.ts`, `PutItemParams` |
| 함수·일반 변수 | camelCase: `putItem`, `refreshToken` |
| 상수 묶음 | `PROVIDER_CODE`, `PATHS`, `DB.TABLE` |
| DB/API 필드 | 기존 snake_case 보존: `provider_srl`, `created_at`, `redirect_uri` |

외부 의존성, 프로젝트 공통 모듈(`@/`), 기능 내 상대 경로(`./`) 순서를 기본으로 주변 import 배치를 따른다. 타입만 필요하면 `import type`을 사용한다. 소스 import의 확장자는 보통 생략한다.

`__index.ts`에서는 기능 주석 바로 아래에 핸들러 import를 두고 해당 route 호출을 이어 쓰는 관례가 있다. 이를 자동 정렬로 모두 파일 위로 이동하지 않는다. 개별 핸들러·클래스는 default export, 헬퍼·상수는 named export를 기본으로 한다.

`_where`, `_values`, `_ready`, `_e`, `__provider__`는 기존 지역 변수 관례다. 수정하는 함수에서 이를 존중하되 모든 새 변수에 밑줄을 강제하지 않는다.

## 스키마와 타입

런타임 스키마는 `__model.ts`에서 정의하고 타입을 파생시킨다. 동일 body를 별도 interface로 다시 선언하지 않는다.

```typescript
import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'

export const ExampleModel = {
  putItemBody: t.Object({
    name: t.String(),
    description: t.Optional(t.String()),
  }),
} as const

export type ExampleModel = {
  [k in keyof typeof ExampleModel]: UnwrapSchema<typeof ExampleModel[k]>
}
```

`BaseModel`과 `libs/validation.ts`의 공통 스키마·패턴을 먼저 확인한다. `strict`, `noUncheckedIndexedAccess`, `noImplicitOverride`, `verbatimModuleSyntax` 설정을 유지한다. 전역 `ZZ`, `Store`, `Context`가 있으나, 새 입력·반환 계약을 무조건 `any`/`ZZ`로 정의하지 말고 가능한 범위를 명시한다. 타입 오류 해결을 이유로 공용 타입이나 tsconfig를 느슨하게 만들지 않는다.

## 라우트와 핸들러 예제

다음은 파일 배치와 스타일을 설명하는 예제다. 실제 구현에는 대상 기능에 맞는 검증·권한·중복 처리를 추가한다.

```typescript
// __index.ts의 라우트 등록 부분
// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({ body: ctx.body })
  return {
    message: 'Complete add Example.',
    data,
  }
}, {
  body: ExampleModel.putItemBody,
})
```

```typescript
// put-item.ts
import ServiceError from '@/classes/ServiceError'
import type { ExampleModel } from './__model'

type PutItemParams = {
  body: ExampleModel['putItemBody']
}

export default async function putItem({ body }: PutItemParams)
{
  try
  {
    // 이 위치에서 기능에 필요한 검증과 저장을 수행한다.
    return { name: body.name }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to add Example.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
```

위 catch 형태는 기존 관례다. `cause`가 실제로 보존되는지는 [현재 한계](current-state.md)를 참고한다. `ctx` 전체를 무조건 전달하거나 실패를 성공 응답으로 삼키지 않는다.

라우트 주석은 🌿 목록, 🌻 상세, 🌱 생성, 🌳 수정, 🍄 삭제, 🌵 처리, 🍁 WebSocket을 사용한다. 주석의 한국어/영어는 주변 맥락에 맞추고, 비자명한 제약과 처리 이유를 설명한다.

## 구조와 동작을 지키는 규칙

- 기존 생성 API가 `PUT /`를 사용하면 같은 계열의 기능도 그 관례를 따른다. 일반 REST 취향으로 기존 API를 `POST`로 바꾸지 않는다.
- PATCH에서 `undefined`(미지정)와 빈 문자열·0·false·null(의도한 값)을 구분한다.
- SQL 값은 바인딩한다. `field`·`order` 같은 SQL 식별자는 바인딩되지 않으므로 허용 목록 등 별도 검증을 설계한다. 기존 문자열 보간은 복사할 규칙이 아니다.
- 기능별 복수 파일 구성을 지킨다. 인증·DB·스키마·HTTP 처리를 거대한 단일 클래스나 `__index.ts`로 합치지 않는다.
- 기능 추가와 공통 리팩터링을 불필요하게 섞지 않는다. 라이브러리 도입·공통 계층 변경은 필요한 이유와 호환성 영향을 설명한다.
