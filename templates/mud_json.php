<?php
$others = array();
foreach ($neighbours->items as $avatar)
{
    $others[] = $avatar->to_array();
}
$msgs = array();
foreach ($chat->items as $msg)
{
    $msgs[] = $msg->to_array();
}
echo json_encode(array(
    'user' => $user->user,
    'area' => $area,
    'neighbours' => $others,
    'chat' => $msgs,
    'last_update' => $last_update
)) ?>
