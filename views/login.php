<?php
require_once '../lib/minim.php';
require_once minim()->models('user');

if ($_SERVER['REQUEST_METHOD'] == 'post')
{
    $user = breve('User')->from($_POST);
    $user->password = md5(@$_POST['password']);
    if ($user->is_valid())
    {
        $_SESSION['user'] = $user->id;
        $next = @$_GET['continue'];
        if (!$next)
        {
            $next = 'home';
        }
        minim()->redirect($next);
    }
}
