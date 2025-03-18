from typing import Optional
from fastapi import UploadFile
from pydantic import BaseModel, Field

class GetIndex(BaseModel):
    fields: Optional[str]
    module: Optional[str]
    module_srl: Optional[int]
    name: Optional[str]
    mime: Optional[str]
    page: Optional[int]
    size: Optional[int]
    order: Optional[str]
    sort: Optional[str]
    unlimited: Optional[bool]

class GetItem(BaseModel):
    srl: int|str

class PutItem(BaseModel):
    module: str
    module_srl: int
    file: UploadFile
    json_data: Optional[str]

class PatchItem(BaseModel):
    srl: int
    module: Optional[str]
    module_srl: Optional[int]
    json_data: Optional[str]
    file: Optional[UploadFile]

class DeleteItem(BaseModel):
    srl: int
