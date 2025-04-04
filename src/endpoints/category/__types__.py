from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str] = None
    name: Optional[str] = None
    module: Optional[str] = None
    module_srl: Optional[int] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False
    mod: Optional[str] = None
    q: Optional[str] = None

class GetItem(BaseModel):
    srl: int
    fields: Optional[str] = None

class PutItem(BaseModel):
    name: str
    module: str
    module_srl: Optional[int] = None

class PatchItem(BaseModel):
    srl: int
    name: Optional[str] = None
    module: Optional[str] = None
    module_srl: Optional[int] = None

class PatchChangeOrder(BaseModel):
    module: str
    module_srl: int
    srls: str

class DeleteItem(BaseModel):
    srl: int
