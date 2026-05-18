import minimist from 'minimist'
import { prompt } from '@/libs/cli'

const argv = minimist(process.argv.slice(2))

// TODO: 인스톨, 언인스톨, 비밀번호 재설정 기능 만들기

console.log('call util.ts', argv)

// if (await prompt('인스톨을 하시겠습니까? (y/n)') === 'y')
// {
//   console.log('OK')
// }
// else
// {
//   console.log('FAIL')
// }

// - 인스톨 검사
// - 인스톨이 안되어 있다면?
//   - 인스톨 할건지 묻기?
