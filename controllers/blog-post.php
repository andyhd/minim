<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('quaver');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');

$date = sprintf("%04d-%02d-%02d", $_GET['year'], $_GET['month'], $_GET['day']);
$filter = array(
    'posted__range' => array("$date 00:00:00", "$date 23:59:59"),
    'slug__eq' => $_GET['slug']
);
$post = breve('BlogPost')->all()->filter($filter)->limit(1);

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
    $comment = breve('BlogComment')->from($_POST);
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
    'comments' => breve('BlogComment')->all()->filter(array('post_id__eq' => $post->items[0]->id))->order_by('-posted'),
    'form' => $form,
    'errors' => $errors,
));
?>
