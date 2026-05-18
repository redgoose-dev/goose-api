import re
from src.libs.db import DB, Table
from ..file import __libs__ as file_libs
from ..tag import __libs__ as tag_libs

def filtering_content(content: str) -> str:
    return content.replace("'", "\\'")

def get_percent_into_checkboxes(body: str) -> int:
    if not body: return 0
    total = len(re.findall(r'\- \[x\]|\- \[ \]', body))
    checked = len(re.findall(r'\- \[x\]', body))
    if not (total > 0 and checked > 0): return 0
    return int(checked / total * 100)

def delete(db: DB, srl: int):
    # file data
    file_libs.delete(db, file_libs.Module.CHECKLIST, srl)
    # tag data
    tag_libs.delete(db, tag_libs.Module.CHECKLIST, srl)
    # checklist data
    db.delete_item(
        table_name=Table.CHECKLIST.value,
        where=[ f'srl = {srl}' ],
    )
