from src.libs.db import DB, Table
from ..file import __libs__ as file_libs

class Module:
    ARTICLE = 'article'

def check_module(db: DB, module: str, srl: int):
    match module:
        case Module.ARTICLE:
            count = db.get_count(
                table_name=Table.ARTICLE.value,
                where=[ f'srl = {srl}' ],
            )
            if not (count > 0): raise Exception('Module not found.', 400)
        case _:
            raise Exception('Module not found.', 400)

# PUBLIC MODULES

def delete(db: DB, srl: int):
    # comment data
    db.delete_item(
        table_name=Table.COMMENT.value,
        where=[ f'srl = {srl}' ],
    )
    # delete files
    file_libs.delete(db, file_libs.Module.COMMENT, srl)

def delete_with_module(db: DB, module: str, module_srl: int):
    match module:
        case Module.ARTICLE:
            comments = db.get_items(
                table_name=Table.COMMENT.value,
                fields=[ 'srl' ],
                where=[
                    f'AND module LIKE \'{module}\'',
                    f'AND module_srl = {module_srl}',
                ],
            )
            if comments and len(comments) > 0:
                for comment in comments: delete(db, comment.get('srl'))
        case _:
            raise Exception('Module not found.')
