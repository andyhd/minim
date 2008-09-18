<?php
require_once '../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('quaver');
require_once minim()->lib('defer');

$date = sprintf("%04d-%02d-%02d", $_GET['year'], $_GET['month'], $_GET['day']);

// get the first post with a matching slug on the specified day
$post = breve('BlogPost')
->filter(array(
    'posted__range' => array("$date 00:00:00", "$date 23:59:59"),
    'slug__eq' => $_GET['slug']
));

// get all comments for the post
$comments = breve('BlogComment')
->filter(array('post_id__eq' => $post->first->id))
->order_by('-posted');

if (!$post->items)
{
    minim('templates')->render_404();
    return;
}

// build the comment form
$form = minim('forms')->form(array('id' => 'comment-form',
                            'class' => 'box'))
               ->hiddenField('post_id', array('initial' => $post->first->id))
               ->textField('name')
               ->textField('email', array('help' => 'Will not be published'))
               ->textArea('content', array('rows' => 6));
minim('log')->debug(print_r($form, TRUE));

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // add a comment
    $form->from($_POST);
    if ($form->isValid())
    {
        $user = breve('User')->from($form->getData());
        $user->save();
        $comment = breve('BlogComment')->from($form->getData());
        $comment->author = $user->id;
        $comment->save();

        minim('user_messaging')->info('Comment saved');
        minim('templates')->redirect('blog-post', $_GET);
    }
    else
    {
        $errors = $form->errors();
    }
}

minim('templates')->render('blog-post', array(
    'post' => $post->items[0],
    'comments' => $comments,
    'form' => $form,
    'errors' => $errors,
));
?>
