import os, secrets, string, pytz, base64, json
from datetime import datetime, timedelta

def create_random_string(length: int) -> str:
    letters = string.ascii_letters + string.digits
    return ''.join(secrets.choice(letters) for i in range(length))

def color_text(text: str, color: str = 'reset') -> str:
    colors = {
        'red': '\033[91m',
        'green': '\033[92m',
        'yellow': '\033[93m',
        'blue': '\033[94m',
        'magenta': '\033[95m',
        'cyan': '\033[96m',
        'white': '\033[97m',
        'reset': '\033[0m'
    }
    return f'{colors[color]}{text}{colors['reset']}'

def get_date() -> str:
    timezone = os.getenv('TIMEZONE')
    date = datetime.now(pytz.timezone(timezone))
    return date.strftime('%Y-%m-%d %H:%M:%S')

def date_format(date: str = None, pattern: str = '%Y-%m-%d %H:%M:%S') -> str:
    if not date: date = get_date()
    try:
        timezone = os.getenv('TIMEZONE')
        date = datetime.strptime(date, '%Y-%m-%d %H:%M:%S')
        local_timezone = pytz.timezone(timezone)
        local_date = date.astimezone(local_timezone)
        return local_date.strftime(pattern)
    except:
        return ''

# 날짜를 x일 앞으로 또는 뒤로 이동한다.
def date_shift(date: str = None, tomorrow: bool = True, day: int = 1) -> str:
    if not date: return ''
    try:
        date = datetime.strptime(date, '%Y-%m-%d %H:%M:%S')
        if tomorrow:
            shifted_date = date + timedelta(days=day)
        else:
            shifted_date = date - timedelta(days=day)
        return shifted_date.strftime('%Y-%m-%d %H:%M:%S')
    except:
        return ''

def get_status_message(code: int) -> str:
    match code:
        case 200:
            return 'OK'
        case 201:
            return 'Created'
        case 202:
            return 'Accepted'
        case 204:
            return 'No Content'
        case 400:
            return 'Bad Request'
        case 401:
            return 'Unauthorized'
        case 403:
            return 'Forbidden'
        case 404:
            return 'Not Found'
        case 405:
            return 'Method Not Allowed'
        case 409:
            return 'Conflict'
        case 422:
            return 'Unprocessable Entity'
        case 455:
            return 'Invalid Data'
        case _:
            return 'Service Error'

def uri_encode(data: dict|list = None) -> str:
    if not data: return ''
    try: return base64.urlsafe_b64encode(json.dumps(data).encode()).decode()
    except: return ''

def uri_decode(_base64: str = None) -> dict|list|None:
    if not _base64: return None
    try: return json.loads(base64.urlsafe_b64decode(_base64.encode()).decode())
    except: return None
