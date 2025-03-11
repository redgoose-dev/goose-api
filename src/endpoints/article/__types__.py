from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    app_srl: Optional[int]
    nest_srl: Optional[int]
    category_srl: Optional[int]
    q: Optional[str]
    mode: Optional[str]
    duration: Optional[str]
    random: Optional[str]
    fields: Optional[str]
    page: Optional[int]
    size: Optional[int]
    order: Optional[str]
    sort: Optional[str]
    unlimited: Optional[bool]

class GetItem(BaseModel):
    srl: int
    fields: Optional[str]
    mode: Optional[str]

class PutItem(BaseModel):
    pass

class PatchItem(BaseModel):
    srl: int
    app_srl: Optional[int]
    nest_srl: Optional[int]
    category_srl: Optional[int]
    title: Optional[str]
    content: Optional[str]
    hit: Optional[bool]
    star: Optional[bool]
    json_data: Optional[str]
    mode: Optional[str]
    regdate: Optional[str]

class DeleteItem(BaseModel):
    srl: int
