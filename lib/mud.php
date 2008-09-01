<?php
function get_last_update()
{
    $u = @$_COOKIE['last_update'];
    if (!$u)
    {
        $u = '0';
    }
    return $u;
}

function update_timestamp()
{
    // set the user's last update time to now
    $ms = microtime();
    $cs = (int)($ms * 1000);
    $now = date('YmdHis') . str_pad($cs, 3, '0', STR_PAD_LEFT);
    setCookie('last_update', $now);
    return $now;
}
