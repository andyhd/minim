<?php
class Minim_Forms implements Minim_Plugin
{
    function form() // {{{
    {
        $argc = func_num_args();
        $argv = func_get_args();
        $model = NULL;
        $params = array();
        if ($argc > 0)
        {
            if (is_array($argv[0]))
            {
                $params = $argv[0];
            }
            if (is_string($argv[0]))
            {
                $model = $argv[0];
                if ($argc > 1 and is_array($argv[1]))
                {
                    $params = $argv[1];
                }
            }
        }
        return new MinimForm($model, $params);
    } // }}}
}
