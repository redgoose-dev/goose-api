from typing import Optional
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    name: Optional[str] = Field(default=None)
    # 값이 없으면 모두, 0이라면 해당되지 않는 값
    category_srl: Optional[int] = Field(default=None)
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')
    page: Optional[int] = Field(default=1)
    size: Optional[int] = Field(default=None)
    order: Optional[str] = Field(default='srl')
    sort: Optional[str] = Field(default='desc', pattern=r'^(asc|desc)$')

class GetItem(BaseModel):
    srl: int
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')

class PutItem(BaseModel):
    category_srl: Optional[int] = Field(default=None)
    name: str
    description: Optional[str] = Field(default=None)
    json_data: str
    path: Optional[str] = Field(default=None)

class PatchItem(BaseModel):
    srl: int
    category_srl: Optional[int] = Field(default=None)
    name: Optional[str] = Field(default=None)
    description: Optional[str] = Field(default=None)
    json_data: Optional[str] = Field(default=None)
    path: Optional[str] = Field(default=None)

class DeleteItem(BaseModel):
    srl: int
