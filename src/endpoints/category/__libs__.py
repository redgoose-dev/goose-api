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

def delete_category_data(db: DB, module: str, srl: int):
    db.delete_item(
        table_name = Table.CATEGORY.value,
        where = [
            f'and module LIKE "{module}"',
            f'and module_srl = {srl}',
        ],
    )
