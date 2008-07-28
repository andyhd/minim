<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('quaver');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');

$post = breve()->manager('BlogPost')->get($_GET['year'], $_GET['month'],
                                          $_GET['day'], $_GET['slug']);

if (!$post->items)
{
    minim()->render_404();
    return;
}

$form = minim()->form('BlogComment', array('id' => 'comment-form',
                                           'class' => 'box'))
               ->hiddenField('post_id', array('initial' => $post->items[0]->id))
               ->textField('name')
               ->textField('email', array('help' => 'Will not be published'))
               ->textArea('content', array('rows' => 6));
minim()->log('form: '.print_r($form, TRUE));

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // add a comment
    $comment = new BlogComment($_POST);
    if ($comment->isValid())
    {
        $comment->save();
    }
    else
    {
        $errors = $comment->errors();
    }
}

minim()->render('blog-post', array(
    'post' => $post->items[0],
    'form' => $form,
    'errors' => $errors,
));
?>
