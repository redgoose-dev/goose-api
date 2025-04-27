from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    name: Optional[str] = None
    module: Optional[str] = None
    module_srl: Optional[int] = None
    page: Optional[int] = 1
    size: Optional[int] = None
    order: Optional[str] = 'srl'
    sort: Optional[str] = 'desc'
    unlimited: Optional[bool] = True

class PutItem(BaseModel):
    module: str
    module_srl: int
    tags: str

class PatchItem(BaseModel):
    module: str
    module_srl: int
    tags: str

class DeleteItem(BaseModel):
    module: str
    module_srl: int
