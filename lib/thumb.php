<?php
function thumbnail($url, $tw=90, $th=90)
{
    $src = FALSE;
    $hash = md5($url);
    $cache = "/tmp/thumb-$hash.png";
    $cached = file_exists($cache);

    // if the thumbnail is cached, cache it
    if (!$cached)
    {   
        // fetch the image
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $img_data = curl_exec($ch);
        curl_close($ch);
        $src = imagecreatefromstring($img_data);

        // if image not found, use a default
        if (FALSE == $src)
        {
            return '/images/fail.php';
        }

        // keep aspect ratio
        $sw = imagesx($src); 
        $sh = imagesy($src);
        $sx = 0;
        $sy = 0;
        if ($sw > $sh)
        {
            $offset = $sw - $sh;
            $sx = floor($offset / 2);
            $sw -= $offset;
        }
        if ($sh > $sw)
        {
            $offset = $sh - $sw;
            $sy = floor($offset / 2);
            $sh -= $offset;
        }

        // generate the thumbnail
        $thumb  = imageCreateTrueColor($tw, $th);
        imagecopyresampled($thumb, $src, 0, 0, $sx, $sy, $tw, $th, $sw, $sh);
        imagepng($thumb, $cache);
    }

    return $cache;
}
