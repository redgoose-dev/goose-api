import os, shutil

# check exist directory
def exist_dir(path: str, use_throw: bool = False) -> bool:
    is_dir = os.path.isdir(path)
    if use_throw and not is_dir:
        raise IsADirectoryError(f"Directory '{path}' does not exist.")
    else:
        return is_dir

# check exist file
def exist_file(path: str, use_throw: bool = False) -> bool:
    is_file = os.path.isfile(path)
    if use_throw and not is_file:
        raise FileNotFoundError(f"File '{path}' does not exist.")
    else:
        return is_file

# create directory
def create_dir(path: str) -> None:
    os.makedirs(path, exist_ok=True)

# copy file
def copy_file(src: str, dst: str) -> None:
    shutil.copy2(src, dst)

# delete directory
def delete_dir(path: str) -> None:
    shutil.rmtree(path)

# delete file
def delete_file(path: str) -> None:
    os.remove(path)
