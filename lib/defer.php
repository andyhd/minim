<?php
class BreveModelSet
{
    var $_model;
    var $_filters;
    var $_cache;
    var $_filter_classes;

    function BreveModelSet($model)
    {
        if (!class_exists($model) or !is_subclass_of($model, 'BreveModel'))
        {
            die("Model $model not found");
        }
        $this->_table = breve()->manager($model)->table;
        $this->_model = $model;
        $this->_filters = array();
        $this->_cache = array();
        $this->_filter_classes = $this->get_filter_classes();
    }

    function filter($kwargs=array())
    {
        foreach ($kwargs as $key => $arg)
        {
            // can't check whether model contains specified fields until we
            // instantiate, but waiting til then might be good anyway.
            // filters are ANDed together
            list($field, $op) = explode('__', $key);
            $class = $this->_class_for($op, $arg);
            $this->_filters[] =& new $class($field, $op, $arg);
        }
        return $this;
    }

    function get_filter_classes()
    {
        static $classes = array();
        if (!$classes)
        {
            // find all available filter types
            foreach (get_declared_classes() as $class)
            {
                if (is_subclass_of($class, 'BreveFilter'))
                {
                    $func = array($class, "register");
                    $reg = call_user_func($func);
                    foreach ($reg as $op => $class)
                    {
                        $classes[$op] = $class;
                    }
                }
            }
        }
        return $classes;
    }

    function _class_for($op, $arg)
    {
        // find filter class for $key
        $classes = $this->get_filter_classes();
        if (!array_key_exists($op, $classes))
        {
            return 'BreveFilter';
        }
        return $classes[$op];
    }

    function _fill_cache()
    {
        list($query, $params) = $this->build_query();
        $this->_cache = $this->execute_query($query, $params);
    }

    function build_query()
    {
        // intended to be overridden to allow use of alternative backends
        $query = array();
        $params = array();
        foreach ($this->_filters as &$filter)
        {
            $query[] = $filter->to_string();
            $params = array_merge($params, $filter->params());
        }
        // TODO - extend to allow OR
        return array(join(' AND ', $query), $params);
    }

    function execute_query($query, $params)
    {
        // intended to be overridden to allow use of alternative backends
        $sql = <<<SQL
            SELECT *
            FROM {$this->_table}
            WHERE {$query}
SQL;
        $s = minim()->db()->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }
}

class BreveFilter
{
    function register()
    {
        // static method, should be overridden
        // returns an array of associative arrays in the form:
        // array(<string> => <classname>)
        // where <string> is the filter query keyword pattern
        $ops = array(
            'eq',
            'ne',
            'gt',
            'gte',
            'lt',
            'lte',
        );
        return array_combine($ops, array_fill(0, count($ops), 'BreveFilter'));
    }

    function BreveFilter($field, $op, $arg)
    {
        $this->field = $field;
        $this->operator = $op;
        $this->value = $arg;
    }

    function to_string()
    {
        $ops = array(
            'eq' => '%s = :%s',
            'ne' => 'NOT (%s = :%s)',
            'gt' => '%s > :%s',
            'gte' => '%s >= :%s',
            'lt' => '%s < :%s',
            'lte' => '%s <= :%s',
        );
        return sprintf($ops[$this->operator], $this->field, $this->field);
    }

    function params()
    {
        return array(":{$this->field}" => $this->value);
    }
}

if (!class_exists('BreveModel'))
{
    require_once 'lib/minim.php';
    require_once minim()->lib('breve');
    require_once minim()->lib('Blog.class');
   
    $ms = new BreveModelSet('BlogPost');
    $ms->filter(array('id__eq' => '1'));
    $ms->_fill_cache();
    print_r($ms);
}
