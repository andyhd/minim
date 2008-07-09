<?php
$config = array(
    'url_map' => array(
        'home' => array(
            'url_pattern' => '^/$',
        ),
        'blog' => array(
            'url_pattern' => '^/blog$',
        ),
        'blog-post' => array(
            'url_pattern' => '^/blog/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})/(?P<slug>[-a-z0-9]+)$',
        ),
    ),
    'database' => array(
        'host' => 'localhost',
        'sock' => '/tmp/mysql.sock',
        'user' => 'root',
        'pass' => '',
        'name' => 'pagezero'
    ),
);
