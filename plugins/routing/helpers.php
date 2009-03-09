<?php
function url_for($route, $params)
{
    return minim('routing')->url_for($route, $params);
}
