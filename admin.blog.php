<?php
require_once 'lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('Blog.class');

$posts = breve()->manager('BlogPost')->all();

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
    'posts' => $posts,
    'errors' => $errors,
));
