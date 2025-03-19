from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    module: Optional[str]
    module_srl: Optional[int]
    content: Optional[str]
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
    content: str
    module: str
    module_srl: int

class PatchItem(BaseModel):
    srl: int
    content: Optional[str]
    module: Optional[str]
    module_srl: Optional[int]

class DeleteItem(BaseModel):
    srl: int
