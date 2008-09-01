<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->models('mud');

$user = $_GET['user']; //minim()->user();

$avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

$area = breve('MudArea')->get($avatar->location)->first;

// get the user's neighbours
$neighbours = breve('MudUser')->filter(array(
    'user__ne' => $user,
    'location__eq' => $avatar->location
));

minim()->render('mud', array(
    'user' => $avatar,
    'area' => $area->id,
    'neighbours' => $neighbours,
));
