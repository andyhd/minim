<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');
require_once minim()->lib('pagination');

if (array_key_exists('id', $_GET) and $edit_id = (int) $_GET['id'])
{
    $post = breve()->manager('BlogPost')->all()->filter(array(
        'id__eq' => $edit_id
    ));
    if (!$post->items)
    {
        minim()->render_404();
    }
    $post = $post->items[0];
}
else
{
    $post = NULL;
}

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // save post
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
    'create' => is_null($post),
    'post' => $post,
    'errors' => $errors,
));
