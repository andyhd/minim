<?php
require_once '../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('mud');

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
