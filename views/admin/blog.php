<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('pagination');

$posts = breve('BlogPost')->all()->order_by('-posted');

$paginator = new BrevePaginator($posts, 'admin/blog');

minim()->render('admin/blog', array(
    'posts' => $paginator,
));
