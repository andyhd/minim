<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_magic_quotes_runtime(0);

$GLOBALS['debug'] = @$_REQUEST['debug'];

require 'lib/minim.php';

minim()->webroot = '/~andy.driver/pagezero';

minim('routing')
    ->map_url('^/$', 'home')
    ->map_url('^/login$', 'login')
    ->map_url('^/logout$', 'logout')
    ->map_url('^/sign-up$', 'sign-up')
    ->map_url('^/blog$', 'blog')
    ->map_url('^/blog/(?P<year>\d{4})/(?P<month>\d{2})/(?P<day>\d{2})/'.
              '(?P<slug>[-a-z0-9]+)$', 'blog-post')
    ->map_url('^/mud$', 'mud')
    ->map_url('^/mud-say$', 'mud-say')
    ->map_url('^/mud-update$', 'mud-update')
    ->map_url('^/mud-move$', 'mud-move');

minim('db')
    ->host('localhost')
    ->socket('/tmp/mysql.sock')
    ->user('root')
    ->password('')
    ->name('pagezero');

minim('admin')->enable();
