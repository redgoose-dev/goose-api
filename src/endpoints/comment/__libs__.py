from src.libs.db import DB, Table

class Module:
    ARTICLE = 'article'

def check_module(db: DB, module: str, srl: int):
    match module:
        case 'article':
            count = db.get_count(
                table_name=Table.ARTICLE.value,
                where=[ f'srl = {srl}' ],
            )
            if not (count > 0): raise Exception('Module not found.', 400)
        case _:
            raise Exception('Module not found.', 400)

# PUBLIC MODULES

def delete(db: DB, module: str, srl: int):
    db.delete_item(
        table_name=Table.COMMENT.value,
        where=[
            f'and module LIKE "{module}"',
            f'and module_srl = {srl}',
        ]
    )
