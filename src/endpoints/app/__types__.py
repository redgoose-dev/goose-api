from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    id: Optional[str] = Field(default=None)
    name: Optional[str] = Field(default=None)
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')
    page: Optional[int] = Field(default=1)
    size: Optional[int] = Field(default=None)
    order: Optional[str] = Field(default='srl')
    sort: Optional[str] = Field(default='desc', pattern=r'^(asc|desc)$')

class GetItem(BaseModel):
    srl: int
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')

class AddItem(BaseModel):
    id: str
    name: str
    description: Optional[str] = Field(default=None)

class PatchItem(BaseModel):
    srl: int
    id: Optional[str] = Field(default=None)
    name: Optional[str] = Field(default=None)
    description: Optional[str] = Field(default=None)

class DeleteItem(BaseModel):
    srl: int
