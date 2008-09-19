<?php
require_once '../config.php';

$date = sprintf("%04d-%02d-%02d", $_GET['year'], $_GET['month'], $_GET['day']);

// get the first post with a matching slug on the specified day
$post = minim->('orm')->BlogPost->filter(array(
    'posted__range' => array("$date 00:00:00", "$date 23:59:59"),
    'slug__eq' => $_GET['slug']
))->first;

if (!$post)
{
    minim('templates')->render_404();
    return;
}

// get all comments for the post
$comments = minim('orm')->BlogComment->filter(array(
    'post_id__eq' => $post->id
))->order_by('-posted');

// build the comment form
$form = minim('forms')->form(array('id' => 'comment-form',
                                   'class' => 'box'))
                      ->hiddenField('post_id', array(
                          'initial' => $post->id))
                      ->textField('name')
                      ->textField('email', array(
                          'help' => 'Will not be published'))
                      ->textArea('content', array('rows' => 6));
minim('log')->debug(print_r($form, TRUE));

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // add a comment
    $form->from($_POST);
    if ($form->isValid())
    {
        $user = minim('orm')->User->from($form->getData());
        $user->save();
        $comment = minim('orm')->BlogComment->from($form->getData());
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
    'post' => $post,
    'comments' => $comments,
    'form' => $form,
    'errors' => $errors,
));
?>
