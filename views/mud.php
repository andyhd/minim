<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->models('user');
require_once minim()->models('mud');

// get the user from the session
$user_id = (int) @$_SESSION['user_id'];
if (!$user_id and @$_GET['user'])
{
    $user_id = $_SESSION['user_id'] = (int) $_GET['user'];
}
$user = breve('MudUser')->filter(array('user__eq' => $user_id))->first;
$chat = breve('MudChat')->filter(array(
    'area__eq' => $user->location,
    'at__gte' => $user->last_update
));

// get the area
$area = breve('MudArea')->get($user->location)->first;

// get the user's neighbours
$neighbours = breve('MudUser')->filter(array(
    'user__ne' => $user->user,
    'location__eq' => $user->location
));

// set the user's last update time to now
$now = (int)((mktime() + array_sum(explode(' ', microtime()))) * 100.0);
minim()->log('now = ' + $now);
$user->last_update = $now;
$user->save();

// called via AJAX?
$template = 'mud';
if (minim()->isXhrRequest())
{
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
    {
        $text = $_POST['says'];
        $msg = breve('MudChat')->from(array(
            'user' => $user->user,
            'area' => $area->id,
            'msg' => $text,
            'at' => $now
        ))->save();
    }
    header('Content-Type: text/json');
    $template = 'mud_json';
}
minim()->render($template, array(
    'user' => $user,
    'area' => $area->id,
    'neighbours' => $neighbours,
    'chat' => $chat,
));
