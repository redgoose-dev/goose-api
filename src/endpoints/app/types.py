from pydantic import BaseModel

# get items
class GetItems(BaseModel):
    pass
class ResponseGetItems(BaseModel):
    pass

# get item
class GetItem(BaseModel):
    id: int


# add item
class AddItem(BaseModel):
    id: str
    name: str
    description: str = None
class ResponseAddItem(BaseModel):
    pass
