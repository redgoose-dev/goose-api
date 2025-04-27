from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    fields: Optional[str] = None
    app_srl: Optional[int] = None
    code: Optional[str] = None
    name: Optional[str] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False
    mod: Optional[str] = None

class GetItem(BaseModel):
    srl: int|str
    fields: Optional[str] = None
    mod: Optional[str] = None

class PutItem(BaseModel):
    app_srl: int
    code: str
    name: str
    description: Optional[str] = None
    json_data: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    app_srl: Optional[int] = None
    code: Optional[str] = None
    name: Optional[str] = None
    description: Optional[str] = None
    json_data: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
