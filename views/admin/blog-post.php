<?php
require_once '../../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');

$action = @$_REQUEST['action'];
$id = (int) @$_REQUEST['id'];

if ($id)
{
    $post = breve('BlogPost')->all()->filter(array(
        'id__eq' => $id
    ));
    if (!$post->items)
    {
        minim('templates')->render_404();
    }
    $post = $post->items[0];
}
else
{
    $post = NULL;
}

if ($action == 'delete')
{
    $post->delete();
    minim('user_messaging')->info("Deleted post \"{$post->title}\"");
    minim('routing')->redirect('admin/blog');
}

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // save post
    $post = breve('BlogPost')->from($_POST);
    $post->author = 1;
    if ($post->isValid())
    {
        $post->save();
        minim('user_messaging')->info("Saved post \"{$post->title}\"");
        minim('routing')->redirect('admin/blog');
    }
    else
    {
        $errors = $post->errors();
    }
}

minim('templates')->render('admin/blog-post-form', array(
    'create' => is_null($post),
    'post' => $post,
    'errors' => $errors,
));
