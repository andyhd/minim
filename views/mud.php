<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->models('user');
require_once minim()->models('mud');

$last_update = @$_COOKIE['last_update'];
if (!$last_update)
{
    $last_update = '0';
}

// get the user from the session
$user = breve('MudUser')->filter(array('user__eq' => $_REQUEST['user']))->first;
$chat = breve('MudChat')->filter(array(
    'area__eq' => $user->location,
    'user__ne' => $user->user,
    'at__gte' => $last_update
));

// get the area
$area = breve('MudArea')->get($user->location)->first;

// get the user's neighbours
$neighbours = breve('MudUser')->filter(array(
    'user__ne' => $user->user,
    'location__eq' => $user->location
));

// set the user's last update time to now
$ms = microtime();
$cs = (int)($ms * 1000);
$now = date('YmdHis') . str_pad($cs, 3, '0', STR_PAD_LEFT);
minim()->log('now = ' + $now);
$last_update = $now;
setCookie('last_update', $now);

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
    'last_update' => $now
));
