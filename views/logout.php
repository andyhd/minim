<?php
require_once '../lib/minim.php';

// unset user cookie
if (minim()->user())
{
    unset($_SESSION['user']);
}

$continue = @$_GET['continue'];
if (!$continue)
{
    $continue = 'home';
}

minim()->redirect($continue);
