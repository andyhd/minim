<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');


minim()->render('blog', array(
    'posts' => breve()->manager('BlogPost')->latest(8),
));
?>
