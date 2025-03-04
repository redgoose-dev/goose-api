from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')
    name: Optional[str] = Field(default=None)
    module: Optional[str] = Field(default='nest', pattern=r'^(nest|json)$')
    target_srl: Optional[int] = Field(default=None)
    page: Optional[int] = Field(default=1)
    size: Optional[int] = Field(default=None)
    order: Optional[str] = Field(default='srl')
    sort: Optional[str] = Field(default='desc', pattern=r'^(asc|desc)$')

class PutItem(BaseModel):
    target_srl: Optional[int] = Field(default=0) # nest_srl,None
    name: str
    module: Optional[str] = Field(default='nest', pattern=r'^(nest|json)$')

class PatchItem(BaseModel):
    srl: int
    target_srl: Optional[int] = Field(default=None) # nest_srl,None
    name: Optional[str] = Field(default=None)
    module: Optional[str] = Field(default=None, pattern=r'^(nest|json)$')

class PatchChangeOrder(BaseModel):
    srl: int
    order: str = Field(pattern=r'^\d+(,\d+)*$') # 1,2,3

class DeleteItem(BaseModel):
    srl: int

