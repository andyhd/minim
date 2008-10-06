<?php
require '../../../config.php';

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    
}

minim('templates')->render('routing', array(
    'url_map' => minim('routing')->_url_map,
));
