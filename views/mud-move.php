<?php
require_once '../config.php';
require_once minim()->lib('mud');

minim()->debug = TRUE;

// get the user from the session
$user = @$_REQUEST['user']; //minim()->user();

$avatar = minim('orm')->MudUser->filter(array('user__eq' => $user))->first;

$last_id = update_timestamp();

$avatar->x = $_REQUEST['x'];
$avatar->y = $_REQUEST['y'];
$avatar->save();
$msg = minim('orm')->MudUpdate->from(array(
    'at' => $last_id,
    'user' => $user,
    'area' => $avatar->location,
    'msg' => "[{$avatar->x},{$avatar->y}]",
    'type' => 0
));
$msg->save();
