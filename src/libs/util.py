import os

# setup env
def setup_env():
    from dotenv import load_dotenv
    load_dotenv('.env')
    load_dotenv('.env.local', override=True)
