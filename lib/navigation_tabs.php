<?php
function navigation()
{
    static $navigation_tabs = array(
        'home' => array(
            'url' => '/',
            'label' => 'Home'
        ),
        'blog' => array(
            'url' => '/blog/',
            'label' => 'Blog'
        )
    );
    $current = '';
    if (preg_match('/index.php$/', $_SERVER['SCRIPT_NAME']))
    {
        $current = 'home';
    }
    if (preg_match('/blog.php$/', $_SERVER['SCRIPT_NAME']))
    {
        $current = 'blog';
    }
    include minim()->template('_navigation');
}
