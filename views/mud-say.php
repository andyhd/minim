<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('mud');
require_once minim()->models('mud');

// get the user from the session
$user = $_REQUEST['user']; //minim()->user();

$avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

$text = $_REQUEST['says'];
$msg = breve('MudUpdate')->from(array(
    'user' => $user,
    'area' => $avatar->location,
    'msg' => $text,
    'at' => update_timestamp(),
    'type' => 1
))->save();
