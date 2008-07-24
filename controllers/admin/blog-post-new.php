<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');
require_once minim()->lib('pagination');

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // add a post
    $post = new BlogPost($_POST);
    $post->author = 1;
    if ($post->isValid())
    {
        $post->save();
        minim()->redirect('admin/blog');
    }
    else
    {
        $errors = $post->errors();
    }
}

minim()->render('admin/blog-post-form', array(
    'create' => TRUE,
    'errors' => $errors,
));
