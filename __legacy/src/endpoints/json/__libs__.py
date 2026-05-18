from src.libs.db import DB, Table
from ..file import __libs__ as file_libs
from ..tag import __libs__ as tag_libs

def get_count(_db: DB, where: list = []) -> int:
    count = _db.get_count(
        table_name = Table.JSON.value,
        where = where,
    )
    return count or 0


# PUBLIC MODULES

def delete(db: DB, srl: int):
    # files
    file_libs.delete(db, file_libs.Module.JSON, srl)
    # tags
    tag_libs.delete(db, tag_libs.Module.JSON, srl)
    # json data
    db.delete_item(
        table_name=Table.JSON.value,
        where=[ f'srl = {srl}' ],
    )
