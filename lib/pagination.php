<?
class BrevePaginator
{
    var $source;
    var $per_page;
    var $page;
    var $max_page;
    var $url;
    var $url_params;

    function BrevePaginator($source, $url, $params=array(), $per_page=20)
    {
        $this->source = $source;
        $this->url = $url;
        $this->url_params = $params;
        $this->per_page = $per_page;
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
    }

    function page($page=NULL)
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
    }

    function __get($name)
    {
        if ($name == 'items')
        {
            $qs = $this->page();
            return $qs->items;
        }
    }

    function max_page()
    {
        if (is_null($this->max_page))
        {
            $this->max_page = floor($this->count() / $this->per_page);
        }
        return $this->max_page;
    }

    function count()
    {
        return $this->source->count();
    }

    function prev($page=NULL)
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
    }

    function next($page=NULL)
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
    }
}
