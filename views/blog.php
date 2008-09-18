<?php
require_once '../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('helpers');

minim('templates')->render('blog', array(
    'posts' => breve('BlogPost')->all()->order_by('-posted')->limit(8),
));
?>
