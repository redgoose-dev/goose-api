from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    name: Optional[str]
    category_srl: Optional[int]
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
    category_srl: Optional[int]
    name: str
    description: Optional[str]
    json_data: str
    path: Optional[str]

class PatchItem(BaseModel):
    srl: int
    category_srl: Optional[int]
    name: Optional[str]
    description: Optional[str]
    json_data: Optional[str]
    path: Optional[str]

class DeleteItem(BaseModel):
    srl: int
