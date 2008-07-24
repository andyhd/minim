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

function paginate($source)
{
    $page = $source->page;
    $from = $page - 2;
    if ($from < 1)
    {
        $from = 1;
    }
    $to = $from + 4;
    if ($to > $source->max_page())
    {
        $to = $source->max_page();
        if ($to - 4 > 1)
        {
            $from = $to - 4;
        }
        else
        {
            $from = 1;
        }
    }
    $out = '';
    if ($source->prev())
    {
        if ($source->prev() == 1)
        {
            $params = $source->url_params;
        }
        else
        {
            $params = array_merge($source->url_params, array(
                'page' => $source->prev()
            ));
        }
        $url = minim()->url_for($source->url, $params);
        $out .= <<<HTML
<li><a href="$url" class="prev">Prev</a></li>
HTML;
    }
    for ($i = $from; $i <= $to; $i++)
    {
        if ($i == $source->page)
        {
            $out .= <<<HTML
<li>$i</li>
HTML;
            continue;
        }
        if ($i == 1)
        {
            $params = $source->url_params;
        }
        else
        {
            $params = array_merge($source->url_params, array(
                'page' => $i
            ));
        }
        $url = minim()->url_for($source->url, $params);
        $out .= <<<HTML
<li><a href="$url">$i</a></li>
HTML;
    }
    if ($source->next())
    {
        $params = array_merge($source->url_params, array(
            'page' => $source->next()
        ));
        $url = minim()->url_for($source->url, $params);
        $out .= <<<HTML
<li><a href="$url" class="next">Next</a></li>
HTML;
    }
    return sprintf('<ul class="pagination">%s</ul>', $out);
}
