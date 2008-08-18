<?php
$this->map_url('^/$', 'home')
     ->map_url('^/blog$', 'blog')
     ->map_url('^/blog/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})/'.
               '(?P<slug>[-a-z0-9]+)$', 'blog-post')
     ->map_url('^/mud$', 'mud')
     ->map_url('^/admin/blog/delete/(?P<id>\d+)$', 'admin/blog-post', 'delete')
     ->map_url('^/admin/blog/edit(?:/(?P<id>\d+))?$', 'admin/blog-post', 'edit')
     ->map_url('^/admin/blog(?:/(?P<page>\d+))?$', 'admin/blog');

$config = array(
    'database' => array(
        'host' => 'localhost',
        'sock' => '/tmp/mysql.sock',
        'user' => 'root',
        'pass' => '',
        'name' => 'pagezero'
    ),
);
