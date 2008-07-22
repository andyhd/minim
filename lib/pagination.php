<?
class BrevePaginator
{
    var $source;
    var $per_page;
    var $page;
    var $max_page;

    function BrevePaginator($source, $per_page=20)
    {
        $this->source = $source;
        minim()->log("Set paginator QuerySet: ".print_r($source, TRUE));
        $this->per_page = $per_page;
        $this->page = 1;
        $this->max_page = NULL;
        if (array_key_exists('page', $_GET))
        {
            $this->page = (int) $_GET['page'];
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
        $start = $page * $this->per_page;
        return $this->source->limit($start, $this->per_page);
    }

    function __get($name)
    {
        if ($name == 'items')
        {
            $qs = $this->page();
            minim()->log('Paginator QuerySet: '.var_export($qs, TRUE));
            return $qs->items;
        }
    }

    function max_page()
    {
        if (is_null($this->max_page))
        {
            $this->max_page = ceil($this->count() / $this->per_page);
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
