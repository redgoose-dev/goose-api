import json
from typing import Any
from fastapi.responses import JSONResponse

class LocalJSONResponse(JSONResponse):
    def __init__(
        self,
        content: Any,
        status_code: int = 200,
        headers: dict = None,
        indent: int = None,
    ):
        self.indent = indent or None
        super().__init__(
            content = content,
            status_code = status_code,
            headers = headers,
        )
    def render(self, content: Any) -> bytes:
        return json.dumps(
            content,
            ensure_ascii = False,
            indent = self.indent,
        ).encode('utf-8')
