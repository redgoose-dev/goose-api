from fastapi import APIRouter, Request, Depends, Form, Query
from typing import List, Dict, Any
from .post_main import post_main

# set router
router = APIRouter()

@router.post('/')
async def main(req: Request, data: List[Dict[str, Any]]):
    return await post_main(params=data, req=req)
