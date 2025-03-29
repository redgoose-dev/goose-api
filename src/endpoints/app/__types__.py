from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    code: Optional[str] = None
    name: Optional[str] = None
    fields: Optional[str] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False

class GetItem(BaseModel):
    srl: int|str
    fields: Optional[str] = None

class PutItem(BaseModel):
    code: str
    name: str
    description: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    code: Optional[str] = None
    name: Optional[str] = None
    description: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
