<?php
function rc4($data, $key)
{
    // key scheduling algorithm
    $key_len = strlen($key);
    $s = range(0, 255);
    $j = 0;
    for ($i = 0; $i < 256; $i++)
    {
        $j = ($j + $s[$i] + ord($key[$i % $key_len])) % 256;
        list($s[$i], $s[$j]) = array($s[$j], $s[$i]); // swap
    }

    // pseudo-random generation algorithm
    $out = '';
    $data_len = strlen($data);
    $i = $j = 0;
    for ($k = 0; $k < $data_len; $k++)
    {
        $i = ($i + 1) % 256;
        $j = ($j + $s[$i]) % 256;
        list($s[$i], $s[$j]) = array($s[$j], $s[$i]);
        $out .= chr(ord($data[$k]) ^ $s[($s[$i] + $s[$j]) % 256]);
    }

    return $out;
}

# $s = 'hello world';
# $e = rc4($s, 'foo');
# echo base64_encode($e); >>> w0eBRadBLF1E5tE=
# $p = rc4($e, 'foo');
# echo $p; >>> hello world
