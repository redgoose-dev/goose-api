from typing import Optional
from pydantic import BaseModel

class GetIndex(BaseModel):
    fields: Optional[str]

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
    user_name: Optional[str]
    user_avatar: Optional[str]
    user_email: str
    user_password: str

class PostLogin(BaseModel):
    user_id: str
    user_password: str

class PatchItem(BaseModel):
    srl: int
    user_id: Optional[str]
    user_name: Optional[str]
    user_avatar: Optional[str]
    user_email: Optional[str]
    user_password: Optional[str]

class DeleteItem(BaseModel):
    srl: int
