from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    fields: Optional[str] = None

class GetRedirect(BaseModel):
    provider: str
    redirect_uri: str

class GetCallback(BaseModel):
    provider: str
    code: str
    state: str

class PostRenew(BaseModel):
    provider: str
    access_token: str
    refresh_token: str

class PutRegister(BaseModel):
    user_id: str
    user_name: Optional[str] = None
    user_avatar: Optional[str] = None
    user_email: str
    user_password: str

class PostLogin(BaseModel):
    user_id: str
    user_password: str

class PatchItem(BaseModel):
    srl: int
    user_id: Optional[str] = None
    user_name: Optional[str] = None
    user_avatar: Optional[str] = None
    user_email: Optional[str] = None
    user_password: Optional[str] = None

class DeleteItem(BaseModel):
    srl: int
