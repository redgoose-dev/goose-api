from src.libs.db import DB, Table

class Module:
    NEST = 'nest'
    JSON = 'json'

def check_module(db: DB, module: str, srl: int|None = None):
    match module:
        case Module.NEST:
            if srl is None: return
            count = db.get_count(
                table_name = Table.NEST.value,
                where = [ f'srl = {srl}' ] if srl else [ f'srl IS NULL' ],
            )
            if not (count > 0): raise Exception('Module not found.', 400)
        case Module.JSON:
            pass
        case _:
            raise Exception('Module not found.', 400)

# PUBLIC MODULES

def delete(db: DB, module: str, srl: int):
    db.delete_item(
        table_name = Table.CATEGORY.value,
        where = [
            f'and module LIKE \'{module}\'',
            f'and module_srl = {srl}',
        ],
    )

def get_item(_db: DB, srl: int, fields: list = []) -> dict|None:
    if not srl: return None
    data = _db.get_item(
        table_name = Table.CATEGORY.value,
        fields = fields,
        where = [ f'srl = {srl}' ],
    )
    return data or None
