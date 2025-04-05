from typing import Optional
from fastapi import UploadFile
from pydantic import BaseModel

class GetIndex(BaseModel):
    fields: Optional[str] = None
    module: Optional[str] = None
    module_srl: Optional[int] = None
    name: Optional[str] = None
    mime: Optional[str] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = False

class GetItem(BaseModel):
    srl: int|str

class PutItem(BaseModel):
    module: str
    module_srl: int
    file: UploadFile
    json_data: Optional[str] = None
    file_format: Optional[str] = None
    file_quality: Optional[int] = 95

class PatchItem(BaseModel):
    srl: int
    module: Optional[str] = None
    module_srl: Optional[int] = None
    json_data: Optional[str] = None
    file: Optional[UploadFile] = None
    file_format: Optional[str] = None
    file_quality: Optional[int] = 95

class DeleteItem(BaseModel):
    srl: int
