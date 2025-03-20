from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    content: Optional[str]
    start: Optional[str]
    end: Optional[str]
    fields: Optional[str]
    page: Optional[int]
    size: Optional[int]
    order: Optional[str]
    sort: Optional[str]
    unlimited: Optional[bool]

class GetItem(BaseModel):
    srl: int
    fields: Optional[str]

class PutItem(BaseModel):
    content: Optional[str]

class PatchItem(BaseModel):
    srl: int
    content: Optional[str]

class DeleteItem(BaseModel):
    srl: int
