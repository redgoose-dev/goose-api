from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    name: Optional[str] = None
    category_srl: Optional[int] = None
    fields: Optional[str] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False
    tag: Optional[str] = None
    mod: Optional[str] = None

class GetItem(BaseModel):
    srl: int
    fields: Optional[str] = None
    mod: Optional[str] = None

class PutItem(BaseModel):
    category_srl: Optional[int] = None
    name: str
    description: Optional[str] = None
    json_data: str
    tag: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    category_srl: Optional[int] = None
    name: Optional[str] = None
    description: Optional[str] = None
    json_data: Optional[str] = None
    tag: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
