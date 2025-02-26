import secrets, string

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

