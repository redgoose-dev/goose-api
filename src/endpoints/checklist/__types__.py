from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
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
    pass

class PatchItem(BaseModel):
    srl: int

class DeleteItem(BaseModel):
    srl: int
