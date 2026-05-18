from typing import Optional
from pydantic import BaseModel

class PatchMain(BaseModel):
    json_data: str
    change_data: bool = False
