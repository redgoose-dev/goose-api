#!/bin/sh

#uvicorn app.main:app --reload --host 0.0.0.0 --port ${PORT}

uvicorn main:app --host 0.0.0.0 --port 8000
