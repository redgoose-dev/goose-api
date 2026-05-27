import { rm, mkdir, exists, readdir, cp } from 'node:fs/promises'
import { Database } from 'bun:sqlite'
import minimist from 'minimist'
import ProviderPassword from '@/classes/provider/Password'
import { PROVIDER_CODE } from '@/libs/provider'
import { prompt, message } from '@/libs/cli'
import { verifyEmail, verifyId } from '@/libs/verify'

/**
 * Command Guide
 *
 * - install: `bun run dev:util install --id goose --name GOOSE --password 1234`
 * - uninstall: `bun run dev:util uninstall`
 * - reset-password: `bun run dev:util reset-password`
 */

const { SERVICE_NAME, PATH_DATA }: ZZ = Bun.env
const argv = minimist(process.argv.slice(2))
const METHOD = {
  INSTALL: 'install',
  UNINSTALL: 'uninstall',
  RESET_PASSWORD: 'reset-password',
}
const paths = {
  data: PATH_DATA,
  seed: './resource/seed.sql',
  preference: './resource/preference.json',
  db: `${PATH_DATA}/db.sqlite`,
}

/**
 * Check install
 * @return {boolean} 검사 요소가 하나라도 없으면 false
 */
async function checkInstall(): Promise<boolean>
{
  const list = await Promise.all([
    exists(`${paths.data}/upload/origin`),
    exists(`${paths.data}/upload/cover`),
    exists(`${paths.data}/cache`),
    exists(`${paths.data}/logs`),
    exists(`${paths.data}/db.sqlite`),
    exists(`${paths.data}/preference.json`),
  ])
  return list.includes(true)
}

async function createAssets()
{
  // make directories
  await Promise.all([
    `${paths.data}/upload/origin`,
    `${paths.data}/upload/cover`,
    `${paths.data}/cache`,
    `${paths.data}/logs`,
  ].map(path => {
    return mkdir(path, { recursive: true })
  }))
  // copy files
  await Promise.all([
    `${paths.preference}`,
  ].map(path => {
    return cp(path, `${paths.data}/preference.json`, { recursive: true })
  }))
}

async function clearAssets()
{
  if (!(await exists(paths.data))) return
  const items = await readdir(paths.data)
  await Promise.all(items.map((name) => {
    return rm(`${paths.data}/${name}`, {
      recursive: true,
      force: true,
    })
  }))
}

function connectDatabase(op: ZZ = { readwrite: true })
{
  return new Database(paths.db, op)
}

async function createDatabase()
{
  try
  {
    const db = connectDatabase({ create: true })
    const seed = Bun.file(paths.seed)
    const seedText = await seed.text()
    db.run(seedText)
    message('run', '데이터베이스를 만들었습니다.')
    return db
  }
  catch (_e: any)
  {
    exit(_e.message, true)
  }
}

/**
 * 계정 정보를 입력받고 그 값들을 리턴한다.
 */
async function inputAccount(): Promise<ZZ>
{
  message('run', '계정정보를 입력해주세요.')
  const id = await inputField({
    ask: '✏️ ID:',
    type: 'id',
    error: 'Invalid ID.',
  })
  const name = await inputField({
    ask: '✏️ Name:',
    error: 'Invalid name.',
  })
  const password = await inputField({
    ask: '✏️ Password:',
    error: 'Invalid password.',
  })
  return { id, name, password }
}
function inputField(op: ZZ): Promise<string>
{
  const { ask, type, error } = op
  return new Promise(async (resolve) => {
    const answer = await prompt(ask)
    if (!answer)
    {
      if (error) message('error', error)
      return resolve(inputField(op))
    }
    switch (type)
    {
      case 'email':
        if (!verifyEmail(answer))
        {
          if (error) message('error', error)
          return resolve(inputField(op))
        }
        break
      case 'id':
        if (!verifyId(answer))
        {
          if (error) message('error', error)
          return resolve(inputField(op))
        }
        break
    }
    resolve(answer)
  })
}

function addAccount(db: Database, account: ZZ)
{
  const { id, name, password } = account
  try
  {
    const sql = `INSERT INTO provider (code, user_id, user_name, user_avatar, user_email, user_password, created_at) VALUES (?, ?, ?, ?, ?, ?, DATETIME("now", "localtime"))`
    db.run(sql, [
      PROVIDER_CODE.PASSWORD,
      id, // user_id
      name, // user_name
      '', // user_email
      '', // user_avatar
      ProviderPassword.hashPassword(password), // user_password
    ])
    message('run', '계정을 추가했습니다.')
  }
  catch (_e: any)
  {
    exit(_e.message, true)
  }
}

function exit(msg: string, error = false)
{
  if (msg) message(error ? 'error' : 'exit', msg)
  process.exit(error ? 1 : 0)
}

// ACTION
switch (argv._[0])
{
  case METHOD.INSTALL:
    message('start', `Starting install ${SERVICE_NAME}!`)
    // check argv
    if (argv.id && argv.name && argv.password)
    {
      // 확인없이 인스톨을 진행한다.
      if (await exists(paths.data)) await clearAssets()
      await createAssets()
      const db = await createDatabase()
      if (db)
      {
        addAccount(db, {
          id: argv.id,
          name: argv.name,
          password: argv.password,
        })
      }
    }
    else
    {
      let answer = ''
      answer = await prompt('정말 인스톨을 진행할까요? [y/N]')
      if (answer.toLowerCase() !== 'y') exit('인스톨 취소', true)
      const installed = await checkInstall()
      if (installed)
      {
        message('warning', `이미 "${SERVICE_NAME}"가 설치되어 있습니다.`)
        answer = await prompt('설치되어있는 데이터를 삭제할까요? [y/N]')
        if (answer.toLowerCase() === 'y') await clearAssets()
        else exit('언인스톨을 취소했습니다.', false)
      }
      const account = await inputAccount()
      await createAssets()
      const db = await createDatabase()
      if (db)
      {
        addAccount(db, account)
        db.close()
      }
    }
    exit('인스톨 완료.', false)
    break
  case METHOD.UNINSTALL:
    message('start', `Starting uninstall ${SERVICE_NAME}!`)
    if (!argv.y)
    {
      let answer = await prompt('정말 언인스톨을 진행할까요? [y/N]')
      if (answer.toLowerCase() !== 'y') exit('언인스톨 취소', true)
    }
    await clearAssets()
    await rm(paths.data, { recursive: true, force: true })
    exit('언인스톨 완료.', false)
    break
  case METHOD.RESET_PASSWORD:
    message('start', `Reset password for ${SERVICE_NAME}!`)
    message('run', '새로운 비밀번호를 입력하세요.')
    const newPassword = await inputField({
      ask: '✏️ Password:',
      error: 'Invalid password.',
    })
    const db = connectDatabase()
    const query = db.query(`UPDATE provider SET user_password = $password WHERE code LIKE $code`)
    query.run({
      '$code': PROVIDER_CODE.PASSWORD,
      '$password': ProviderPassword.hashPassword(newPassword),
    })
    if (db) db.close()
    exit('비밀번호 재설정 완료.', false)
    break
  default:
    exit('메서드가 없습니다.', true)
}
