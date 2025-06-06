import sys, sqlite3
from src.libs import file
from src.libs import string

# set values
args = sys.argv[1:]
skip = True if '-y' in args else False
installed = False
resource_path = {
    'data': './data',
    'data/upload': './data/upload',
    'data/upload/origin': './data/upload/origin',
    'data/upload/cover': './data/upload/cover',
    'data/cache': './data/cache',
    'data/log': './data/log',
    'resource/seed.sql': './resource/seed.sql',
    'data/db.sqlite': './data/db.sqlite',
    'resource/preference.json': './resource/preference.json',
    'data/preference.json': './data/preference.json',
}

# checking install
def checking_install() -> str|None:
    print('⏳ Checking Install.')
    is_data_dir = file.exist_dir('./data', False)
    status = None
    if not is_data_dir:
        status = 'NOT'
    else:
        try:
            file.exist_dir(resource_path['data'], True)
            file.exist_dir(resource_path['data/upload'], True)
            file.exist_dir(resource_path['data/upload/origin'], True)
            file.exist_dir(resource_path['data/upload/cover'], True)
            file.exist_dir(resource_path['data/cache'], True)
            file.exist_dir(resource_path['data/log'], True)
            file.exist_file(resource_path['data/db.sqlite'], True)
            file.exist_file(resource_path['data/preference.json'], True)
            status = 'OK'
        except Exception as e:
            print(string.color_text(f'⚠️ {e}', 'red'))
            status = 'ERROR'
    print(f'✅ Checking Install Complete! "{status}"')
    return status or 'OK'

# install resource
def install_resource():
    print('⏳ Setup resource start.')
    file.create_dir(resource_path['data'])
    file.create_dir(resource_path['data/upload'])
    file.create_dir(resource_path['data/upload/origin'])
    file.create_dir(resource_path['data/upload/cover'])
    file.create_dir(resource_path['data/cache'])
    file.create_dir(resource_path['data/log'])
    file.copy_file(resource_path['resource/preference.json'], resource_path['data/preference.json'])
    print('✅ Setup resource complete!')

def destroy_resource():
    print('🚧 Destroying resource...')
    file.delete_dir(resource_path['data'], True)
    print('✅ Destroying resource complete!')

# install db
def install_db():
    print('⏳ Install DB start.')
    conn = sqlite3.connect(resource_path['data/db.sqlite'])
    cursor = conn.cursor()
    with open(resource_path['resource/seed.sql'], 'r', encoding='utf-8') as sql_file:
        sql_script = sql_file.read()
    cursor.executescript(sql_script)
    conn.commit()
    conn.close()
    print('✅ Install DB complete!')

# print result
def print_result(_installed: bool):
    if _installed:
        print('🙌🏼 Congratulation! Setup complete!')
    else:
        print('❌ Cancelled install!')


### ▶️ACTION ###

# check install
check_install = checking_install()

# setup resource
if check_install == 'NOT':
    answer = 'y' if skip else input('⭐ Do you want to install? (Y/n): ')
    if answer.lower() != 'n':
        install_resource()
        install_db()
        installed = True
elif check_install == 'ERROR':
    answer = 'y' if skip else input('⭐ Broken resource. Do you want to reinstall? (Y/n): ')
    if answer.lower() != 'n':
        destroy_resource()
        install_resource()
        install_db()
        installed = True
elif check_install == 'OK':
    answer = 'n' if skip else input('⭐ It\'s currently installed. Do you want to reinstall? (y/N): ')
    if answer.lower() == 'y':
        destroy_resource()
        install_resource()
        install_db()
        installed = True

# print result
print_result(installed)
