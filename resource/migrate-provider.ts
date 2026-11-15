import { Database } from 'bun:sqlite'

export function migrateProvider(db: Database): boolean
{
  return db.transaction(() => {
    const columns = db.query<{ name: string }, []>('PRAGMA table_info(provider)').all().map(column => column.name)
    const legacy = [ 'user_id', 'user_name', 'user_avatar', 'user_email', 'user_password' ]
    if (columns.includes('uid') && columns.includes('data') && legacy.every(column => !columns.includes(column))) return false
    if (!legacy.every(column => columns.includes(column)) || columns.includes('uid') || columns.includes('data'))
    {
      throw new Error('Unsupported provider schema.')
    }

    db.run(`ALTER TABLE provider ADD COLUMN data TEXT NOT NULL DEFAULT '{}' CHECK (json_valid(data) AND json_type(data) = 'object')`)
    db.run(`UPDATE provider SET data = json_object('name', user_name, 'avatar', user_avatar, 'email', user_email, 'password', user_password)`)
    db.run('ALTER TABLE provider RENAME COLUMN user_id TO uid')
    db.run('DROP INDEX IF EXISTS idx_provider_user_id')
    db.run('CREATE INDEX idx_provider_uid ON provider(uid)')
    db.run('ALTER TABLE provider DROP COLUMN user_name')
    db.run('ALTER TABLE provider DROP COLUMN user_avatar')
    db.run('ALTER TABLE provider DROP COLUMN user_email')
    db.run('ALTER TABLE provider DROP COLUMN user_password')
    return true
  }).immediate()
}

if (import.meta.main)
{
  const path = process.argv[2]
  if (!path) throw new Error('Usage: bun run resource/migrate-provider.ts /absolute/path/db.sqlite')
  const db = new Database(path, { readwrite: true })
  try
  {
    console.log(migrateProvider(db) ? 'Provider migration complete.' : 'Provider schema is already current.')
  }
  finally
  {
    db.close()
  }
}
