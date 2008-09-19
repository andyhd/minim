<?php
require_once '../../config.php';

$posts = minim('orm')->BlogPost->all()->order_by('-posted');

$paginator = new BrevePaginator($posts, 'admin/blog');

minim('templates')->render('admin/blog', array(
    'posts' => $paginator,
));
