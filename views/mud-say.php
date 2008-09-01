<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('mud');
require_once minim()->models('mud');

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    // get the user from the session
    $user = $_POST['user']; //minim()->user();

    $avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

    $text = $_POST['says'];
    $msg = breve('MudChat')->from(array(
        'user' => $user,
        'area' => $avatar->location,
        'msg' => $text,
        'at' => get_last_update()
    ))->save();
}
else
{
    header('Status: 500');
}
