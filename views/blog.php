<?php
require_once '../config.php';
require_once minim()->lib('helpers');

minim('templates')->render('blog', array(
    'posts' => minim('orm')->BlogPost->all()->order_by('-posted')->limit(8),
));
?>
