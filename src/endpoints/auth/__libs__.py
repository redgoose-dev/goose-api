import os, time, hashlib

def hash_password(pw: str, salt: bytes = None) -> str:
    if salt is None: salt = os.urandom(16)
    # 패스워드와 salt를 결합하여 해시화
    hash_object = hashlib.pbkdf2_hmac('sha256', pw.encode('utf-8'), salt, 100000)
    # salt와 해시값을 결합하여 반환
    hashed_password = salt + hash_object
    return hashed_password.hex()

def verify_password(pw: str, hashed_pw: str) -> bool:
    # 저장된 패스워드에서 salt와 해시값 분리
    stored_password_bytes = bytes.fromhex(pw)
    salt = stored_password_bytes[:16]
    stored_hash = stored_password_bytes[16:]
    # 제공된 패스워드를 동일한 방식으로 해시화
    hash_object = hashlib.pbkdf2_hmac('sha256', hashed_pw.encode('utf-8'), salt, 100000)
    return stored_hash == hash_object

def create_token(mode: str) -> str:
    current_time = str(int(time.time()))
    token_data = mode + current_time + mode
    token_hash = hashlib.sha256(token_data.encode('utf-8')).hexdigest()
    return 'xx' + token_hash[4:28] + 'xx' if mode == 'access' else token_hash
