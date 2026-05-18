import json

def check_url(url: str) -> bool:
    if url.startswith('http://') or url.startswith('https://'):
        return True
    else:
        raise Exception('URL validation error.')
