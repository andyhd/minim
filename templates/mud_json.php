<?php echo json_encode(array(
    'user' => $user,
    'area' => $area,
    'neighbours' => $neighbours->items,
)) ?>
