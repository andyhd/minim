<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('mud');
require_once minim()->models('mud');

minim()->debug = TRUE;

// get the user from the session
$user = @$_REQUEST['user']; //minim()->user();

$avatar = breve('MudUser')->filter(array('user__eq' => $user))->first;

$last_id = update_timestamp();

$avatar->x = $_REQUEST['x'];
$avatar->y = $_REQUEST['y'];
$avatar->save();
$msg = breve('MudUpdate')->from(array(
    'at' => $last_id,
    'user' => $user,
    'area' => $avatar->location,
    'msg' => "[{$avatar->x},{$avatar->y}]",
    'type' => 0
));
$msg->save();

if (minim()->debug)
{
    print '<pre class="debug">'.join("\n", minim()->log_msgs)."</pre>";
}
