from src.libs.db import DB, Table

def check_module(db: DB, module: str, srl: int):
    match module:
        case 'article':
            count = db.get_count(
                table_name = Table.ARTICLE.value,
                where = [ f'srl = {srl}' ],
            )
            if not (count > 0): raise Exception('Module not found.', 400)
        case _:
            raise Exception('Module not found.', 400)
