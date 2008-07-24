<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');
require_once minim()->lib('pagination');

$posts = breve()->manager('BlogPost')->all();

$paginator = new BrevePaginator($posts, 'admin/blog');

minim()->render('admin/blog', array(
    'posts' => $paginator,
));
