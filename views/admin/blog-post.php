<?php
require_once '../../config.php';

$action = @$_REQUEST['action'];
$id = (int) @$_REQUEST['id'];

if ($id)
{
    $post = minim('orm')->BlogPost->all()->filter(array(
        'id__eq' => $id
    ))->first;
    if (!$post)
    {
        minim('templates')->render_404();
    }
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
    $post = minim('orm')->BlogPost->from($_POST);
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
