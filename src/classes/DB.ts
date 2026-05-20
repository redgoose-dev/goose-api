class DB {

  public _db: any
  public table: ZZ = {}

  constructor()
  {}

}

export default DB
export const db: InstanceType<typeof DB> = new DB()
