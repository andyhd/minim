<?php
require_once '../config.php';

minim('templates')->render('home', array(
    'logo_is_h1' => true
));
