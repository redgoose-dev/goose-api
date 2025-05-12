import os, io, json, pillow_avif
from typing import Optional
from PIL import Image, ImageOps
from src import libs, output, __DEV__
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types, __libs__ as file_libs

async def get_item(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetItem(**params)

        # set srl
        srl: Optional[int] = None
        code: Optional[str] = None
        try: srl = int(params.srl)
        except ValueError: code = str(params.srl)

        # set local values
        use_cache = code is not None # 코드값으로 사용하면 캐시 사용
        tail = make_tail(_w=params.w, _h=params.h, _t=params.t, _q=params.q)

        # set where
        if code is not None:
            where = [ f'code GLOB "{code}"' ]
        elif srl is not None:
            where = [ f'srl = {srl}' ]
        else:
            raise Exception('srl or code is required', 404)

        # init data
        data = {
            'path': None,
            'mime': None,
            'buffer': None,
        }

        # 캐시파일에서 데이터 가져오기
        if use_cache:
            cache_file = get_cache_path(get_cache_filename(code, tail))
            if file_libs.exist_file(cache_file):
                cache = file_libs.open_file(cache_file, 'json')
                if file_libs.exist_file(cache.get('path')):
                    # check auth
                    if cache.get('private'): checking_token(req, db)
                    # set data
                    data['path'] = cache.get('cache_path') or cache.get('path')
                    data['mime'] = cache.get('mime')
                else:
                    # 문제가 있는 파일이라고 판단하여 캐시파일을 삭제한다.
                    file_libs.delete_file(cache_file)

        # 캐시파일에서 가져온 데이터가 없다면?
        if not (data.get('path') and data.get('mime')):
            # get file
            file = db.get_item(
                table_name=Table.FILE.value,
                where=where,
            )
            if not file:
                raise Exception('Not found file data.', 404)
            if not file_libs.exist_file(file.get('path')):
                raise Exception('Not found file.', 404)
            # get module
            module = file_libs.get_module(db, file.get('module'), file.get('module_srl'))
            if not module: raise Exception('Not found module data.', 404)
            # set status
            status = file_libs.Status.filter(module.get('mode', None))
            # switching status
            match status:
                case file_libs.Status.PRIVATE | file_libs.Status.PUBLIC:
                    # check auth
                    if status == file_libs.Status.PRIVATE: checking_token(req, db)
                    # get new data
                    if file.get('mime').startswith('image/') and tail:
                        new_data = await resize_image(
                            path=file.get('path'),
                            code=file.get('code'),
                            tail=tail,
                            mime=file.get('mime'),
                        )
                        new_data['path'] = file.get('path')
                    else:
                        new_data = {
                            'path': file.get('path'),
                            'mime': file.get('mime'),
                        }
                    # create cache file
                    if use_cache:
                        cache_file = get_cache_filename(file.get('code'), tail)
                        await make_cache(cache_file, {
                            'code': file.get('code'),
                            'module': file.get('module'),
                            'module_srl': file.get('module_srl'),
                            'private': status == file_libs.Status.PRIVATE,
                            'path': file.get('path'),
                            'cache_path': new_data.get('cache_path') if new_data.get('cache_path') else None,
                            'name': file_libs.change_file_extension(file.get('name'), new_data.get('mime').split('/')[-1]),
                            'mime': new_data.get('mime'),
                        })
                    # retry set data
                    data['path'] = new_data.get('cache_path') if new_data.get('cache_path') else new_data.get('path')
                    data['mime'] = new_data.get('mime')
                    if 'buffer' in new_data: data['buffer'] = new_data['buffer']
                case file_libs.Status.READY:
                    data['path'] = file.get('path')
                    data['mime'] = file.get('mime')
                case _:
                    raise Exception('Can not open file.', 404)

        # check output data
        if not (data.get('path') and data.get('mime')):
            raise Exception('Not found file data.', 404)

        # 버퍼 데이터를 만든다.
        if not data.get('buffer') and data.get('path'):
            data['buffer'] = file_libs.convert_path_to_buffer(data.get('path'))

        # check buffer data
        if not data.get('buffer'):
            raise Exception('Not found buffer data.', 404)

        # set result
        headers = {
            'Content-Type': data.get('mime'),
            'Content-Length': str(len(data.get('buffer'))),
        }
        if not __DEV__: headers['Cache-Control'] = 'public, max-age=2592000'
        result = output.buffer(
            data.get('buffer'),
            options={ 'headers': headers, 'log': False },
            _req=req,
            _log=False,
        )

    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result

def make_tail(_w: int, _h: int, _t: str, _q: int) -> dict|None:
    min_size = 100
    min_quality = 0
    if not ((_w and _w > min_size) or (_h and _h > min_size)): return None
    arr = []
    if _w and _w > min_size: arr.append(f'w={_w}')
    if _h and _h > min_size: arr.append(f'h={_h}')
    if _t: arr.append(f't={_t}')
    if _q and _q > min_quality: arr.append(f'q={_q}')
    return {
        'query': '&'.join(arr) if len(arr) > 0 else None,
        'options': { 'w': _w, 'h': _h, 't': _t, 'q': _q },
    }

async def make_cache(path: str, data: dict):
    path = get_cache_path(path)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    with open(path, 'w') as f:
        json.dump(obj=data, fp=f, ensure_ascii=False, indent=2)

async def resize_image(path: str, code: str, tail: dict, mime: str) -> dict:
    tail_query = tail.get('query')
    tail_options = tail.get('options')
    # get dir name
    dir_name = path.split(libs.dir_upload + '/', 1)[-1]
    dir_name = dir_name.split('/')[0]
    # set destination path
    dest_path = f'{libs.cache_path}/{dir_name}/{code}__{tail_query}'
    if file_libs.exist_file(dest_path):
        buffer = file_libs.convert_path_to_buffer(dest_path)
        return {
            'path': dest_path,
            'buffer': buffer,
            'mime': mime,
        }
    else:
        # open image
        image = Image.open(path)
        # run resize
        _w = tail_options.get('w')
        _h = tail_options.get('h')
        _t = tail_options.get('t') or 'contain'
        _q = tail_options.get('q') or 85
        _resample = Image.Resampling.LANCZOS
        match _t:
            case 'contain':
                if _w and _h:
                    image.thumbnail((_w, _h), _resample)
                elif _w:
                    ratio = _w / float(image.size[0])
                    _h = round(image.size[1] * ratio)
                    image.thumbnail((_w, _h), _resample)
                elif _h:
                    ratio = _h / float(image.size[1])
                    _w = round(image.size[0] * ratio)
                    image.thumbnail((_w, _h), _resample)
            case 'stretch':
                if not _w: _w = _h
                if not _h: _h = _w
                image = image.resize((_w, _h), _resample)
            case 'cover':
                if not _w: _w = _h
                if not _h: _h = _w
                image = ImageOps.fit(image, (_w, _h), method=_resample)
        _format = 'webp' if _q > 85 else 'avif'
        # save buffer
        output_io = io.BytesIO()
        image.save(output_io, format=_format, quality=_q)
        buffer = output_io.getvalue()
        # save file
        os.makedirs(os.path.dirname(dest_path), exist_ok=True)
        image.save(dest_path, format=_format, quality=_q)
        return {
            'cache_path': dest_path,
            'buffer': buffer,
            'mime': f'image/{_format}',
        }

def get_cache_path(filename: str) -> str:
    return f'{libs.cache_path}/json/{filename}'

def get_cache_filename(code: str, tail: dict|None) -> str:
    query = tail.get('query') if tail else ''
    if not query: return f'{code}.json'
    return f'{code}__{query}.json'
