<?php
function logged_in_user()
{
    return minim('auth')->get_logged_in_user();
}
