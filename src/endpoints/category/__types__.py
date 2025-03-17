from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str]
    name: Optional[str]
    module: Optional[str]
    module_srl: Optional[int]
    page: Optional[int]
    size: Optional[int]
    order: Optional[str]
    sort: Optional[str]
    unlimited: Optional[bool]

class GetItem(BaseModel):
    srl: int
    fields: Optional[str]

class PutItem(BaseModel):
    name: str
    module: str
    module_srl: Optional[int]

class PatchItem(BaseModel):
    srl: int
    name: Optional[str]
    module: Optional[str]
    module_srl: Optional[int]

class PatchChangeOrder(BaseModel):
    module: str
    module_srl: int
    srls: str

class DeleteItem(BaseModel):
    srl: int
