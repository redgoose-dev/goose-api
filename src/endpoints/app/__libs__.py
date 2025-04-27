from src.libs.db import DB, Table

def get_item(_db: DB, srl: int, fields: list = []) -> dict|None:
    if not srl: return None
    data = _db.get_item(
        table_name = Table.APP.value,
        fields = fields,
        where = [ f'srl = {srl}' ],
    )
    return data or None
