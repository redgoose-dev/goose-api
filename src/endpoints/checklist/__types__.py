from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    content: Optional[str] = None
    start: Optional[str] = None
    end: Optional[str] = None
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
    content: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    content: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
