<?php
function url_for_blog_post($post)
{
    $ts = strtotime($post["posted"]);
    list($year, $month, $day) = explode(" ", date("Y m d", $ts));
    return "/~andy.driver/pagezero/blog.php?year=$year&month=$month&day=$day&slug={$post['slug']}";
    return minim()->url_for("blog-post", array(
        "year" => $year,
        "month" => $month,
        "day" => $day,
        "slug" => $post["slug"]
    ));
}
