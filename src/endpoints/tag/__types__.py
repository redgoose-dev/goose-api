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

class GetItem(BaseModel):
    pass

class PutItem(BaseModel):
    pass

class PatchItem(BaseModel):
    pass

class DeleteItem(BaseModel):
    pass
