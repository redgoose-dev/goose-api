from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    app_srl: Optional[int] = None
    nest_srl: Optional[int] = None
    category_srl: Optional[int] = None
    q: Optional[str] = None
    mode: Optional[str] = None
    duration: Optional[str] = None
    random: Optional[str] = None
    fields: Optional[str] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False
    tag: Optional[str] = None

class GetItem(BaseModel):
    srl: int
    fields: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    app_srl: Optional[int] = None
    nest_srl: Optional[int] = None
    category_srl: Optional[int] = None
    title: Optional[str] = None
    content: Optional[str] = None
    hit: Optional[bool] = False
    star: Optional[bool] = False
    json_data: Optional[str] = None
    tag: Optional[str] = None
    mode: Optional[str] = None
    regdate: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
