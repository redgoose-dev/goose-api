from typing import Optional
from pydantic import BaseModel

class PutItem(BaseModel):
    description: Optional[str] = None

class GetIndex(BaseModel):
    token: Optional[str] = None

class PatchItem(BaseModel):
    srl: int
    description: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
