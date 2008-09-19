<?php
require_once '../config.php';

$user = minim()->user();

$avatar = minim('orm')->MudUser->filter(array('user__eq' => $user))->first;

$area = minim('orm')->MudArea->get($avatar->location)->first;

// get the user's neighbours
$neighbours = minim('orm')->MudUser->filter(array(
    'user__ne' => $user,
    'location__eq' => $avatar->location
));

minim('templates')->render('mud', array(
    'user' => $avatar,
    'area' => $area->id,
    'neighbours' => $neighbours->to_array(),
));
