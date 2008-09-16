<?php
require_once '../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');

minim('templates')->render('blog', array(
    'posts' => breve('BlogPost')->all()->order_by('-posted')->limit(8),
));
?>
