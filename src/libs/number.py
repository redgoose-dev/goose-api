def time_to_seconds(date_type: str, num: int) -> int:
    match date_type:
        case 'day':
            return num * 86400
        case 'hour':
            return num * 3600
        case 'minute':
            return num * 60
        case _:
            return num
