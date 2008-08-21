<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->models('user');
require_once minim()->models('mud');

// get the user from the session
$user = breve('MudUser')->filter(array('user__eq' => 1));//$_SESSION['user_id']));
if ($user->count() == 0)
{
    // user not found

}
$user = $user->first;

// get the area
$area = breve('MudArea')->get($user->location)->first;

// get the user's neighbours
$neighbours = breve('MudUser')->filter(array(
    'id__ne' => $user->id,
    'location__eq' => $user->location
));

// called via AJAX?
$template = 'mud';
if (minim()->isXhrRequest())
{
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
    {
        $user = $_POST['user'];
        $text = $_POST['says'];
    }
    $template = 'mud_json';
}
minim()->render($template, array(
    'user' => $user,
    'area' => $area,
    'neighbours' => $neighbours,
));
