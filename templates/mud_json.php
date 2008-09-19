<?php
$others = array();
foreach ($neighbours as $avatar)
{
    $others[] = $avatar->to_array();
}
$msgs = array();
foreach ($chat as $msg)
{
    $msgs[] = $msg->to_array();
}
echo json_encode(array(
    'user' => $user->to_array(),
    'area' => $area,
    'neighbours' => $others,
    'chat' => $msgs,
    'last_update' => $last_update
)) ?>
