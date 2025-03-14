from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str]
    app_srl: Optional[int]
    code: Optional[str]
    name: Optional[str]
    page: Optional[int]
    size: Optional[int]
    order: Optional[str]
    sort: Optional[str]
    unlimited: Optional[bool]

class GetItem(BaseModel):
    srl: int|str
    fields: Optional[str]

class PutItem(BaseModel):
    app_srl: int
    code: str
    name: str
    description: Optional[str]
    json_data: Optional[str]

class PatchItem(BaseModel):
    srl: int
    app_srl: Optional[int]
    code: Optional[str]
    name: Optional[str]
    description: Optional[str]
    json_data: Optional[str]

class DeleteItem(BaseModel):
    srl: int
