import os, time, hashlib
from src.libs.number import time_to_seconds

class ProviderPassword:

    # set values
    name = 'password'

    @staticmethod
    def hash_password(pw: str, salt: bytes = None) -> str:
        if salt is None: salt = os.urandom(16)
        # 패스워드와 salt를 결합하여 해시화
        hash_object = hashlib.pbkdf2_hmac('sha256', pw.encode('utf-8'), salt, 100000)
        # salt와 해시값을 결합하여 반환
        hashed_password = salt + hash_object
        return hashed_password.hex()

    @staticmethod
    def verify_password(pw: str, hashed_pw: str) -> bool:
        # 저장된 패스워드에서 salt와 해시값 분리
        stored_password_bytes = bytes.fromhex(pw)
        salt = stored_password_bytes[:16]
        stored_hash = stored_password_bytes[16:]
        # 제공된 패스워드를 동일한 방식으로 해시화
        hash_object = hashlib.pbkdf2_hmac('sha256', hashed_pw.encode('utf-8'), salt, 100000)
        return stored_hash == hash_object

    @staticmethod
    def new_token(mode: str) -> str:
        current_time = str(int(time.time()))
        token_data = mode + current_time + mode
        token_hash = hashlib.sha256(token_data.encode('utf-8')).hexdigest()
        return 'xx' + token_hash[4:28] + 'xx' if mode == 'access' else token_hash

    @staticmethod
    def create_token() -> dict:
        access_token = ProviderPassword.new_token('access')
        expires = time_to_seconds('day', 7)
        refresh_token = ProviderPassword.new_token('refresh')
        return {
            'access': access_token,
            'expires': expires,
            'refresh': refresh_token,
        }

    async def renew_access_token(self, refresh_token = None) -> dict|None:
        token = self.create_token()
        return {
            'access': token['access'],
            'refresh': token['refresh'],
            'expires': token['expires'],
        }
