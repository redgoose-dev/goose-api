from typing import Optional
from pydantic import BaseModel

class PutItem(BaseModel):
    description: Optional[str] = None

class GetIndex(BaseModel):
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    token: Optional[str] = None
    mod: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    description: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
