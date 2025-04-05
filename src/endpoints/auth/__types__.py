from typing import Optional
from pydantic import BaseModel

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


class PostReadyLogin(BaseModel):
    redirect_uri: str

class PostLogin(BaseModel):
    user_id: str
    user_password: str


class PutProvider(BaseModel):
    user_id: str
    user_name: Optional[str] = None
    user_avatar: Optional[str] = None
    user_email: str
    user_password: str

class PostProviderIndex(BaseModel):
    redirect_uri: str

class PatchProviderItem(BaseModel):
    srl: int
    user_id: Optional[str] = None
    user_name: Optional[str] = None
    user_avatar: Optional[str] = None
    user_email: Optional[str] = None
    user_password: Optional[str] = None

class DeleteProviderItem(BaseModel):
    srl: int
