<?php
require_once '../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');

$user = $_GET['user']; //minim()->user();

$avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

$area = breve('MudArea')->get($avatar->location)->first;

// get the user's neighbours
$neighbours = breve('MudUser')->filter(array(
    'user__ne' => $user,
    'location__eq' => $avatar->location
));

minim('templates')->render('mud', array(
    'user' => $avatar,
    'area' => $area->id,
    'neighbours' => $neighbours->to_array(),
));
