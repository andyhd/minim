<?php
require_once '../lib/minim.php';

// unset user cookie
unset($_COOKIE['user']);

$continue = @$_GET['continue'];
if (!$continue)
{
    $continue = 'home';
}

minim()->redirect($continue);
