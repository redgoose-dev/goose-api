<?php
namespace Core;
use Exception, GdImage;

if (!defined('__API_GOOSE__')) exit();

/**
 * get resize image
 *
 * @query string $_GET[path] 이미지 주소
 * @query string $_GET[type] 리사이즈 방식 (contain,cover)
 * @query number $_GET[width] 가로 사이즈
 * @query number $_GET[height] 세로 사이즈
 *
 * @var Goose|Connect $this
 */

$headerImage = 'Content-type: image/webp';
$PATHS = (object)[
  'upload' => '/data/upload/',
  'cache' => '/data/cache/',
];

try
{
  $params = (object)[
    'path' => $this->get->path ?? null,
    'type' => $this->get->type ?? null,
    'width' => isset($this->get->width) ? min((int)$this->get->width, 1000) : 0,
    'height' => isset($this->get->height) ? min((int)$this->get->height, 1000) : 0,
    'quality' => isset($this->get->quality) ? min((int)$this->get->quality, 100) : 90,
  ];
  // check path value
  if (!$params->path) throw new Exception(Message::make('error.notFound', 'path'));
  // get path
  $mainPath = getPath($params);
  $pathDest = __API_PATH__.$PATHS->cache.$mainPath->dir.'/'.$mainPath->name;
  // check exist cache file
  if (file_exists($pathDest))
  {
    header($headerImage);
    readfile($pathDest);
  }
  else if (file_exists(__API_PATH__.$PATHS->upload.$params->path))
  {
    // resize
    $result = resizeImage(__API_PATH__.$PATHS->upload.$params->path, $params->width, $params->height, $params->type);
    // make directory
    File::makeDirectory(__API_PATH__.$PATHS->cache.$mainPath->dir, 0707, true);
    // save image file
    imagewebp($result, $pathDest, $params->quality);
    // print image
    header($headerImage);
    imagewebp($result, null, $params->quality);
    // destroy image
    imagedestroy($result);
  }
  else
  {
    Error::raw(404);
  }
}
catch (Exception $e)
{
  Error::raw($e->getCode());
}

/**
 * get dest path
 */
function getPath(object $params): object
{
  $path = $params->path;
  $query = [];
  if ($params->type) $query[] = 't='.$params->type;
  if ($params->width) $query[] = 'w='.$params->width;
  if ($params->height) $query[] = 'h='.$params->height;
  if ($params->quality) $query[] = 'q='.$params->quality;
  $query = join('&', $query);
  $parts = explode('/', $path);
  $filename = array_pop($parts);
  $dir = implode('/', $parts);
  return (object)[
    'dir' => $dir,
    'name' => $filename.($query ? '.'.$query : '').'.webp',
  ];
}

/**
 * get size contain
 */
function getSizeContain(array $info, int $cropWidth, int $cropHeight): object
{
  $ratio = $info[0] / $info[1];
  $pos = (object)[
    'destX' => 0,
    'destY' => 0,
    'srcX' => 0,
    'srcY' => 0,
  ];
  $pos->destX = $pos->destY = 0;
  $pos->srcWidth = $info[0];
  $pos->srcHeight = $info[1];
  if ($cropWidth > 0 && $cropHeight === 0)
  {
    $newWidth = $cropWidth;
    $newHeight = $cropWidth / $ratio;
  }
  else if ($cropHeight > 0 && $cropWidth === 0)
  {
    $newWidth = $cropHeight * $ratio;
    $newHeight = $cropHeight;
  }
  else
  {
    $cropWidth = $cropWidth > 0 ? $cropWidth : 320;
    $cropHeight = $cropHeight > 0 ? $cropHeight : 240;
    if ($cropWidth < $cropHeight)
    {
      $newWidth = $cropHeight * $ratio;
      $newHeight = $cropHeight;
    }
    else
    {
      $newWidth = $cropWidth;
      $newHeight = $cropWidth / $ratio;
    }
  }
  $pos->width = $pos->destWidth = (int)$newWidth;
  $pos->height = $pos->destHeight = (int)$newHeight;
  return $pos;
}
/**
 * get size cover
 */
function getSizeCover(array $info, int $cropWidth, int $cropHeight): object
{
  $ratio = $info[0] / $info[1];
  $pos = (object)[
    'destX' => 0,
    'destY' => 0,
  ];
  $cropWidth = $cropWidth > 0 ? $cropWidth : 320;
  $cropHeight = $cropHeight > 0 ? $cropHeight : 240;
  $pos->width = $pos->destWidth = $cropWidth;
  $pos->height = $pos->destHeight = $cropHeight;
  $destRatio = $cropWidth / $cropHeight;
  if ($ratio >= $destRatio)
  {
    $pos->srcHeight = $info[1];
    $pos->srcWidth = ceil(($info[1] * $cropWidth) / $cropHeight);
    $pos->srcX = ceil(($info[0] - $pos->srcWidth) / 2);
    $pos->srcY = 0;
  }
  else
  {
    $pos->srcWidth = $info[0];
    $pos->srcHeight = ceil(($pos->srcWidth * $cropHeight) / $cropWidth);
    $pos->srcY = ceil(($info[1] - $pos->srcHeight) / 2);
    $pos->srcX = 0;
  }
  return $pos;
}

/**
 * @throws Exception
 */
function resizeImage(string $path, int $w, int $h, string|null $type): GdImage
{
  $imageInfo = getimagesize($path);
  $mime = explode('/', $imageInfo['mime']);
  if ($mime[0] !== 'image') throw new Exception(Message::make('file.NOT_IMAGE'));
  // create GdImage
  $src = getImage($mime[1], $path);
  if (!$src) throw new Exception(Message::make('file.INVALID_FILE'));
  // get size info
  $pos = match($type)
  {
    'cover' => getSizeCover($imageInfo, $w, $h),
    default => getSizeContain($imageInfo, $w, $h),
  };
  // resize
  $dest = imagecreatetruecolor($pos->width, $pos->height);
  imagecopyresampled(
    $dest,
    $src,
    $pos->destX,
    $pos->destY,
    $pos->srcX,
    $pos->srcY,
    $pos->destWidth,
    $pos->destHeight,
    $pos->srcWidth,
    $pos->srcHeight
  );
  return $dest;
}

/**
 * get image
 * path to GdImage
 */
function getImage(string $mime, string $path): GdImage|null
{
  return match ($mime)
  {
    'png' => imagecreatefrompng($path),
    'jpg', 'jpeg' => imagecreatefromjpeg($path),
    'gif' => imagecreatefromgif($path),
    'webp' => imagecreatefromwebp($path),
    default => null,
  };
}
