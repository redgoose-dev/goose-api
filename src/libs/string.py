import secrets, string

def create_random_string(length: int) -> str:
    letters = string.ascii_letters + string.digits
    return ''.join(secrets.choice(letters) for i in range(length))
