<?php
function navigation()
{
    static $navigation_tabs = array(
        'home' => 'Home',
        'blog' => 'Blog',
    );
    $current = '';
    if (preg_match('/home.php$/', $_SERVER['SCRIPT_NAME']))
    {
        $current = 'home';
    }
    if (preg_match('/blog.*?.php$/', $_SERVER['SCRIPT_NAME']))
    {
        $current = 'blog';
    }
    include minim()->template('_navigation');
}
