<?php
require '../lib/thumb.php';
header('Content-Type: image/png');

$image = urldecode($_GET['url']);
$thumb = thumbnail($image);
readfile($thumb);
