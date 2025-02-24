import os
from fastapi import FastAPI
from dotenv import load_dotenv
from api.main import router

# setup env
load_dotenv('.env')
load_dotenv('.env.local', override=True)

# set fastapi app
app = FastAPI()

# setup router
app.include_router(router)
