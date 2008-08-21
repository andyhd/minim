<?php
$others = array();
foreach ($neighbours->items as $avatar)
{
    $others[] = $avatar->to_array();
}
echo json_encode(array(
    'user' => (int)$user,
    'area' => $area->id,
    'neighbours' => $others,
)) ?>
