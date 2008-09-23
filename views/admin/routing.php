<?php
require '../../config.php';

minim('templates')->render('admin/routing', array(
    'url_map' => minim('routing')->_url_map,
));
