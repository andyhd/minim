<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');
require_once minim()->lib('pagination');

$posts = breve()->manager('BlogPost')->all();

$paginator = new BrevePaginator($posts, 'admin/blog');

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // add a post
    $post = new BlogPost($_POST);
    if ($post->isValid())
    {
        $post->save();
    }
    else
    {
        $errors = $comment->errors();
    }
}

minim()->render('admin/blog', array(
    'posts' => $paginator,
    'errors' => $errors,
));
