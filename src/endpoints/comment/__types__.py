from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    module: Optional[str] = None
    module_srl: Optional[int] = None
    content: Optional[str] = None
    fields: Optional[str] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False

class GetItem(BaseModel):
    srl: int
    fields: Optional[str] = None

class PutItem(BaseModel):
    content: str
    module: str
    module_srl: int

class PatchItem(BaseModel):
    srl: int
    content: Optional[str] = None
    module: Optional[str] = None
    module_srl: Optional[int] = None

class DeleteItem(BaseModel):
    srl: int
