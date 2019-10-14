<?php
$extensions = ['php'];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$ext = pathinfo($path, PATHINFO_EXTENSION);

if (in_array($ext, $extensions) || !$ext)
{
  require_once 'index.php';
}
else
{
  $file = $_SERVER['SCRIPT_FILENAME'];
  $type = mime_content_type($file);
  header('Access-Control-Allow-Origin: *');
  header('Content-Type: '.$type);
  header('Content-Length: '.filesize($file));
  readfile($file);
}
