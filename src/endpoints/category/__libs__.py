from src.libs.db import DB, Table

def check_module(db: DB, module: str, srl: int):
    match module:
        case 'nest':
            count = db.get_count(
                table_name = Table.NEST.value,
                where = [f'srl = {srl}'],
            )
            if not (count > 0): raise Exception('Module not found.', 400)
        case 'json':
            pass
        case _:
            raise Exception('Module not found.', 400)
