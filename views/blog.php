<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->models('blog');
require_once minim()->models('user');

minim()->render('blog', array(
    'posts' => breve('BlogPost')->all()->order_by('-posted')->limit(8),
));
?>
