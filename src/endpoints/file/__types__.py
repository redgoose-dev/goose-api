from typing import Optional
from fastapi import UploadFile
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str] = Field(default=None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$')
    module: Optional[str] = Field(default=None)
    target_srl: Optional[int]
    name: Optional[str] = Field(default=None)
    type: Optional[str] = Field(default=None)
    page: Optional[int] = Field(default=1)
    size: Optional[int] = Field(default=None)
    order: Optional[str] = Field(default='srl')
    sort: Optional[str] = Field(default='desc', pattern=r'^(asc|desc)$')

class GetItem(BaseModel):
    srl: int

class PutItem(BaseModel):
    target_srl: int
    module: str
    json_data: Optional[str] = Field(default='{}')
    file: UploadFile

class PatchItem(BaseModel):
    srl: int
    target_srl: Optional[int] = Field(default=None)
    module: Optional[str] = Field(default=None)
    json_data: Optional[str] = Field(default=None)
    file: Optional[UploadFile] = Field(default=None)

class DeleteItem(BaseModel):
    srl: int|str
