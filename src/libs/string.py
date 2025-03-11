import os, secrets, string, pytz
from datetime import datetime

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
    if not date: return ''
    try:
        timezone = os.getenv('TIMEZONE')
        date = datetime.strptime(date, '%Y-%m-%d %H:%M:%S')
        local_timezone = pytz.timezone(timezone)
        local_date = date.astimezone(local_timezone)
        return local_date.strftime(pattern)
    except:
        return ''

def convert_date(date: str = None) -> str:
    if not date: return ''
    try:
        timezone = os.getenv('TIMEZONE')
        date = datetime.strptime(date, '%Y-%m-%d %H:%M:%S')
        date_utc = date.replace(tzinfo=pytz.UTC)
        local_timezone = pytz.timezone(timezone)
        local_date = date_utc.astimezone(local_timezone)
        return local_date.strftime('%Y-%m-%d %H:%M:%S')
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
