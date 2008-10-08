<?php
require_once '../../config.php';

if (@$_REQUEST['action'] == 'new')
{
    $params = array();
}
else
{
    $post = minim('orm')->BlogPost;
    if ($post)
    {
        $post = $post->filter(array(
            'id__eq' => (int) @$_REQUEST['id']
        ))->first;
    }
    if (!$post)
    {
        minim('templates')->render_404();
        return;
    }

    if (@$_REQUEST['action'] == 'delete')
    {
        $post->delete();
        minim('user_messaging')->info("Deleted post \"{$post->title}\"");
        minim('routing')->redirect('admin/blog');
    }

    $params = array('instance' => $post);
}

$form = minim('forms')->form('BlogPost', $params);

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $post = minim('orm')->BlogPost->from($form->getData());
        $post->save();

        minim('user_messaging')->info("Saved post \"{$post->title}\"");
        minim('routing')->redirect('admin/blog', $_GET);
    }
    else
    {
        $errors = $form->errors();
        minim('user_messaging')->info('Errors in form');
    }
}

minim('templates')->render('admin/blog-post-form', array(
    'form' => $form,
    'errors' => $errors,
));
