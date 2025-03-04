from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')
    app_srl: Optional[int] = Field(default=None)
    code: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z0-9-_]+$')
    name: Optional[str] = Field(default=None)
    page: Optional[int] = Field(default=1)
    size: Optional[int] = Field(default=None)
    order: Optional[str] = Field(default='srl')
    sort: Optional[str] = Field(default='desc', pattern=r'^(asc|desc)$')

class GetItem(BaseModel):
    srl: int|str
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')

class PutItem(BaseModel):
    app_srl: int
    code: str = Field(pattern=r'^[a-zA-Z0-9-_]+$')
    name: str
    description: Optional[str] = Field(default=None)
    json_data: Optional[str] = Field(default=None)

class PatchItem(BaseModel):
    srl: int
    app_srl: Optional[int] = Field(default=None)
    code: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z0-9-_]+$')
    name: Optional[str] = Field(default=None)
    description: Optional[str] = Field(default=None)
    json_data: Optional[str] = Field(default=None)

class DeleteItem(BaseModel):
    srl: int
