from src.libs.db import DB, Table

def get_count(_db: DB, where: list = []) -> int:
    count = _db.get_count(
        table_name = Table.JSON.value,
        where = where,
    )
    return count or 0
