<?php
require_once 'lib/minim.php';
require minim()->lib('Blog.class');
require minim()->fixture('blog'); 

$posts = Blog::getRecentPosts(5);

minim()->render('blog', array(
    'posts' => $posts,
));
?>
