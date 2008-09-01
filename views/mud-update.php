<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('mud');
require_once minim()->models('mud');

// get the user from the session
$user = $_GET['user']; //minim()->user();

$avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

// get any chat messages since last update
$chat = breve('MudChat')->filter(array(
    'area__eq' => $avatar->location,
    'user__ne' => $user,
    'at__gte' => get_last_update()
));

// get the area
$area = breve('MudArea')->get($avatar->location)->first;

// get the user's neighbours
$neighbours = breve('MudUser')->filter(array(
    'user__ne' => $user,
    'location__eq' => $avatar->location
));

// set the user's last update time to now
$last_update = update_timestamp();

// if the user's x and y coords have changed, update
$x = @$_REQUEST['x'];
$y = @$_REQUEST['y'];
if (($x and $x != $avatar->x) or ($y and $y != $avatar->y))
{
    $avatar->x = $x;
    $avatar->y = $y;
    $avatar->save();
}

header('Content-Type: text/json');
minim()->render('mud_json', array(
    'user' => $avatar,
    'area' => $area->id,
    'neighbours' => $neighbours,
    'chat' => $chat,
    'last_update' => $last_update
));
