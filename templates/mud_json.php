<?php
$others = array();
foreach ($neighbours->items as $avatar)
{
    $others[] = array_merge($avatar->to_array(), array('says' => 'hi!'));
}
echo json_encode(array(
    'user' => (int)$user,
    'area' => $area->id,
    'neighbours' => $others,
)) ?>
