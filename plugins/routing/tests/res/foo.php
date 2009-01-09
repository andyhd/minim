<?php
// test view
echo 'foo';
if (@$_GET['id'])
{
    echo $_GET['id'];
}
