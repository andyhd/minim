<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve');
require_once minim()->lib('defer');
require_once minim()->lib('Blog.class');

$post_params = array('year', 'month', 'day', 'slug');
if (array_intersect(array_keys($_GET), $post_params) == $post_params)
{
    $post = breve()->manager('BlogPost')->get($_GET['year'], $_GET['month'],
                                              $_GET['day'], $_GET['slug']);

    if (!$post->items)
    {
        minim()->render_404();
        return;
    }

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
        'errors' => $errors,
    ));
}
else
{
    minim()->render('blog', array(
        'posts' => breve()->manager('BlogPost')->latest(8),
    ));
}
?>
