<?php
require_once '../../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');

$posts = breve('BlogPost')->all()->order_by('-posted');

$paginator = new BrevePaginator($posts, 'admin/blog');

minim('templates')->render('admin/blog', array(
    'posts' => $paginator,
));
