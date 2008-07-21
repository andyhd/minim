<?php
function url_for_blog_post($post)
{
    list($year, $month, $day) = explode(" ", date("Y m d", $post->posted));
    return minim()->url_for("blog-post", array(
        "year" => $year,
        "month" => $month,
        "day" => $day,
        "slug" => $post->slug
    ));
}

function alternate($str1, $str2)
{
    static $toggle = True;
    $toggle = !$toggle;
    return $toggle ? $str1 : $str2;
}
