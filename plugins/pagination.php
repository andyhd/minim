<?php
class Minim_Paginator implements Minim_Plugin
{
    var $source;
    var $per_page;
    var $page;
    var $max_page;
    var $url;
    var $url_params;

    function Minim_Paginator() // {{{
    {
        $this->source = NULL;
        $this->url = NULL;
        $this->url_params = array();
        $this->per_page = 20;
        $this->page = 1;
        $this->max_page = NULL;
        if (array_key_exists('page', $_GET))
        {
            $this->page = (int) $_GET['page'];
            if ($this->page < 1)
            {
                $this->page = 1;
            }
        }
    } // }}}

    function source($source=NULL) // {{{
    {
        if ($source)
        {
            $this->source = $source;
            return $this;
        }
        return $this->source;
    } // }}}

    function base_url($url=NULL, $params=array()) // {{{
    {
        if ($url)
        {
            $this->url = $url;
            $this->url_params = $params;
            return $this;
        }
        return $this->url;
    } // }}}

    function per_page($per_page=NULL) // {{{
    {
        if ($per_page)
        {
            $this->per_page = $per_page;
            return $this;
        }
        return $this->per_page;
    } // }}}

    function page($page=NULL) // {{{
    {
        if (is_null($page))
        {
            $page = $this->page;
        }
        if ($page < 1)
        {
            $page = 1;
        }
        $start = ($page - 1) * $this->per_page;
        return $this->source->limit($start, $this->per_page);
    } // }}}

    function __get($name) // {{{
    {
        if ($name == 'items')
        {
            $qs = $this->page();
            return $qs->items;
        }
    } // }}}

    function max_page() // {{{
    {
        if (is_null($this->max_page))
        {
            $this->max_page = floor($this->count() / $this->per_page);
        }
        return $this->max_page;
    } // }}}

    function count() // {{{
    {
        return $this->source->count();
    } // }}}

    function prev($page=NULL) // {{{
    {
        if (is_null($page))
        {
            $page = $this->page;
        }
        if ($page > 1)
        {
            return $page - 1;
        }
        return False;
    } // }}}

    function next($page=NULL) // {{{
    {
        if (is_null($page))
        {
            $page = $this->page;
        }
        if ($page < $this->max_page)
        {
            return $page + 1;
        }
        return False;
    } // }}}

    function paginate($source) // {{{
    {
        $page = $this->page;
        $from = $page - 2;
        if ($from < 1)
        {
            $from = 1;
        }
        $to = $from + 4;
        if ($to > $this->max_page())
        {
            $to = $this->max_page();
            if ($to - 4 > 1)
            {
                $from = $to - 4;
            }
            else
            {
                $from = 1;
            }
        }
        $prev = NULL;
        $next = NULL;
        if ($this->prev())
        {
            if ($this->prev() == 1)
            {
                $params = $this->url_params;
            }
            else
            {
                $params = array_merge($this->url_params, array(
                    'page' => $this->prev()
                ));
            }
            $prev = minim('routing')->url_for($this->url, $params);
        }
        $url = array();
        for ($i = $from; $i <= $to; $i++)
        {
            if ($i == 1)
            {
                $params = $this->url_params;
            }
            else
            {
                $params = array_merge($this->url_params, array(
                    'page' => $i
                ));
            }
            $url[$i] = minim('routing')->url_for($this->url, $params);
        }
        if ($this->next())
        {
            $params = array_merge($this->url_params, array(
                'page' => $this->next()
            ));
            $next = minim('routing')->url_for($this->url, $params);
        }
        
        include minim()->root."/templates/_pagination.php";
    } // }}}
}
