<?php
function url_for_blog_post($post)
{
    list($year, $month, $day) = explode(" ", date("Y m d", $post->posted));
    return "/~andy.driver/pagezero/blog.php?year=$year&month=$month&day=$day&slug={$post->slug}";
    return minim()->url_for("blog-post", array(
        "year" => $year,
        "month" => $month,
        "day" => $day,
        "slug" => $post->slug
    ));
}
