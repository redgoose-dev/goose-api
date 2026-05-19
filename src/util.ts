import { rm, mkdir, exists, readdir, cp } from 'node:fs/promises'
import { Database } from 'bun:sqlite'
import minimist from 'minimist'
import { PROVIDER_CODE } from '@/libs/provider'
import { prompt, message } from '@/libs/cli'
import ProviderPassword from '@/classes/provider/Password'

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

async function checkInstall()
{
  // TODO
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
  const items = await readdir(paths.data)
  await Promise.all(items.map((name) => {
    return rm(`${paths.data}/${name}`, {
      recursive: true,
      force: true,
    })
  }))
}

async function createDatabase()
{
  try
  {
    const db = new Database(paths.db, { create: true })
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
      ProviderPassword.prototype.hashPassword(String(password)), // user_password
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

// Start install
message('start', `Starting install ${SERVICE_NAME}!`)

// ACTION
switch (argv._[0])
{
  case METHOD.INSTALL:
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
      // TODO: 정말로 인스톨할건지 물어보기
      // TODO: 인스톨 검사 ? 언인스톨
      // TODO: 계정정보 입력받기
      // TODO: 에셋 만들기
      // TODO: 데이터베이스 만들기
      // TODO: 계정 만들기
    }
    break
  case METHOD.UNINSTALL:
    // TODO: 언인스톨
    break
  case METHOD.RESET_PASSWORD:
    // TODO: 비밀번호 리셋
    break
  default:
    exit('메서드가 없습니다.', true)
}

// Exit install
exit('Complete install.', false)
