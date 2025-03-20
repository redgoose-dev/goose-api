import re

def filtering_content(content: str) -> str:
    return content.replace("'", "\\'")

def get_percent_into_checkboxes(body: str) -> int:
    if not body: return 0
    total = len(re.findall(r'\- \[x\]|\- \[ \]', body))
    checked = len(re.findall(r'\- \[x\]', body))
    if not (total > 0 and checked > 0): return 0
    return int(checked / total * 100)
