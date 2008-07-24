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
        'admin/blog-post-delete' => array(
            'url_pattern' => '^/admin/blog/delete/(?P<id>\d+)$',
        ),
        'admin/blog-post-edit' => array(
            'url_pattern' => '^/admin/blog/edit(?:/(?P<id>\d+))?$',
        ),
        'admin/blog' => array(
            'url_pattern' => '^/admin/blog(?:/(?P<page>\d+))?$',
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
