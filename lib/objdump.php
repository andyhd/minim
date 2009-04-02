<?php
$dump_already_seen = array();

function dump($var, $recurse_depth=0)
{
    global $dump_already_seen;

    $out = '';
    if (is_object($var))
    {
        $hash = md5($var);
        $class = get_class($var);
        $out = "$class object: {";
        if (in_array($hash, $dump_already_seen))
        {
            $out .= '**RECURSION**';
        }
        else
        {
            $dump_already_seen[] = $hash;
            $i = 0;
            foreach ($var as $key => $val)
            {
                if ($key[0] != '_')
                {
                    $out .= $i++ ? ', ' : '';
                    $out .= "$key: ".dump($val, $recurse_depth+1);
                }
            }
        }
        $out .= '}';
    }
    elseif (is_array($var))
    {
        $i = 0;
        $out .= '[';
        foreach ($var as $key => $val)
        {
            $out .= $i++ ? ', ' : '';
            $out .= "$key => ".dump($val, $recurse_depth+1);
        }
        $out .= ']';
    }
    else
    {
        $out = var_export($var, TRUE);
    }
    if ($recurse_depth == 0)
    {
        $dump_already_seen = array();
    }
    return $out;
}

function indent($str)
{
    return preg_replace('/^/m', '    ', $str);
}
