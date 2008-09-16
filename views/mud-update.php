<?php
header('Content-Type: text/plain');

require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('mud');

// get the user from the session
$user = @$_GET['user']; //minim()->user();
$avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

$last_id = @$_SESSION['last_id'];
if (!$last_id)
{
    $last_id = update_timestamp();
}

$msgs = array();
$start = time();
while (!$msgs and (time() - $start) < 1)
{
    // get any changes since last update
    $msgs = breve('MudUpdate')->filter(array(
        'area__eq' => $avatar->location,
        'user__ne' => $user,
        'at__gte' => $last_id
    ))->to_array();

    if ($msgs)
    {
        $last_id = update_timestamp();
        $_SESSION['last_id'] = $last_id;

        // output json
        echo json_encode(array(
            'result' => $msgs,
            'last_id' => $last_id
        )), "\n";
        
        flush();
        break;
    }
    usleep(500000); // 0.5s polling interval
}
if (!$msgs)
{
    echo json_encode(array(
        'result' => array(),
        'last_id' => $last_id,
        'debug' => join("\n", minim()->log_msgs)
    ));
}
