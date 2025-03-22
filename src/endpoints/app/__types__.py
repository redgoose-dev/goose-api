from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    code: Optional[str]
    name: Optional[str]
    fields: Optional[str]
    page: Optional[int]
    size: Optional[int]
    order: Optional[str]
    sort: Optional[str]
    unlimited: Optional[bool]

class GetItem(BaseModel):
    srl: int|str
    fields: Optional[str]

class AddItem(BaseModel):
    code: str
    name: str
    description: Optional[str]

class PatchItem(BaseModel):
    srl: int
    code: Optional[str]
    name: Optional[str]
    description: Optional[str]

class DeleteItem(BaseModel):
    srl: int
