import inspect, sys, json, time
from loguru import logger
from src import libs, __USE_LOG__, __RECORD_LOG__

def setup():
    if not __USE_LOG__: return
    logger.remove()
    if __RECORD_LOG__:
        # success
        logger.add(
            libs.log_path + '/success/{time:YYYY-MM-DD}.log',
            format='☘️ {time:YYYY-MM-DD HH:mm:ss} | {extra[method]} {extra[url]} | {message}\n{extra[options]}',
            filter=lambda record: record['level'].name == 'SUCCESS',
            rotation='05:00',
            retention='7 days',
            compression='zip',
            level='SUCCESS',
            encoding='utf-8',
        )
        # error
        logger.add(
            libs.log_path + '/error/{time:YYYY-MM-DD}.log',
            format='⚠️ {time:YYYY-MM-DD HH:mm:ss} | {extra[error_code]} | {extra[method]} {extra[url]} | {message}\n{extra[options]}{extra[stack]}',
            filter=lambda record: record['level'].name == 'ERROR',
            rotation='05:00',
            retention='7 days',
            compression='zip',
            level='ERROR',
            encoding='utf-8',
        )
    else:
        # success
        logger.add(
            sys.stderr,
            format='☘️ {time:YYYY-MM-DD HH:mm:ss} | {extra[method]} {extra[url]} | {message}\n{extra[options]}',
            level='SUCCESS',
            filter=lambda record: record['level'].name == 'SUCCESS',
        )
        # error
        logger.add(
            sys.stderr,
            format='⚠️ {time:YYYY-MM-DD HH:mm:ss} | {extra[error_code]} | {extra[method]} {extra[url]} | {message}\n{extra[options]}{extra[stack]}',
            level='ERROR',
            filter=lambda record: record['level'].name == 'ERROR',
        )

    # print command line
    logger.add(
        sys.stderr,
        format='🌻 {time:YYYY-MM-DD HH:mm:ss} | {message}{extra[options]}',
        filter=lambda record: record['level'].name == 'INFO',
    )

def success(
    message: str,
    url: str,
    method: str = 'GET',
    **options
):
    if not __USE_LOG__: return
    # get caller module name
    caller_module = inspect.stack()[2].frame.f_globals['__name__']
    # call logger
    logger.success(
        message,
        method=method,
        url=url,
        options={
            'module': caller_module,
            'status_code': options.get('status_code', 200),
            'ip': options.get('ip', None),
            'user_agent': options.get('user_agent', 'Unknown'),
            'run_time': options.get('run_time', None),
        },
    )

def error(
    message: str,
    url: str,
    method: str = 'GET',
    error_code: str = None,
    **options
):
    if not __USE_LOG__: return
    # get caller module name
    caller_module = inspect.stack()[2].frame.f_globals['__name__']
    # set stack
    stack = options.get('stack').rstrip() if options.get('stack') else None
    # call logger
    logger.error(
        message,
        method=method,
        url=url,
        error_code=error_code,
        stack=f'\n{stack}' if stack else None,
        options={
            'module': caller_module,
            'status_code': options.get('status_code', 500),
            'ip': options.get('ip', None),
            'user_agent': options.get('user_agent', 'Unknown'),
            'run_time': options.get('run_time', None),
        },
    )

def cmd(
    message: str,
    **options
):
    if not __USE_LOG__: return
    _options = {}
    logger.info(
        message,
        options=_options if len(_options) > 0 else None,
    )
