from typing import Optional
from pydantic import BaseModel
from fastapi import WebSocket

class GetIndex(BaseModel):
    fields: Optional[str]

class GetRedirect(BaseModel):
    provider: str
    redirect_uri: str

class GetCallback(BaseModel):
    provider: str
    code: str
    state: str

class DeleteItem(BaseModel):
    srl: int

class PostChecking(BaseModel):
    pass
