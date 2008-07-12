<?php
require_once 'lib/minim.php';
require minim()->lib('Blog.class');
require minim()->fixture('blog'); 

$post_params = array('year', 'month', 'day', 'slug');
if (array_intersect(array_keys($_GET), $post_params) == $post_params)
{
    $post = BlogPost::manager()->get($_GET['year'], $_GET['month'],
                                     $_GET['day'], $_GET['slug']);

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        // add a comment
    }

    minim()->render('blog-post', array(
        'post' => $post,
        'comments' => $post->comments,
    ));
}
else
{
    minim()->render('blog', array(
        'posts' => BlogPost::manager()->getRecent(5),
    ));
}
?>
