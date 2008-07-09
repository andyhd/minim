<?php
require_once 'lib/minim.php';
require minim()->lib('Blog.class');
require minim()->fixture('blog'); 

$post_params = array('year', 'month', 'day', 'slug');
if (array_intersect(array_keys($_GET), $post_params) == $post_params)
{
    minim()->render('blog-post', array(
        'post' => Blog::getPost($_GET['year'], $_GET['month'], $_GET['day'],
                                $_GET['slug'])
    ));
}
else
{
    minim()->render('blog', array(
        'posts' => Blog::getRecentPosts(5),
    ));
}
?>
