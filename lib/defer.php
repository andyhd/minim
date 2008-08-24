<?php
class BreveModelSet
{
    var $_model;
    var $_filters;
    var $_sorting;
    var $_start;
    var $_num;
    var $_cache;
    var $_count;
    var $_filter_classes;

    function BreveModelSet($model)
    {
#        if (!class_exists($model) or !is_subclass_of($model, 'BreveModel'))
#        {
#            die("Model $model not found");
#        }
        $this->_table = breve($model)->table();
        $this->_model = $model;
        $this->_filters = array();
        $this->_sorting = array();
        $this->_start = 0;
        $this->_num = 0;
        $this->_cache = array();
        $this->_count = NULL;
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

    function order_by()
    {
        $args = func_get_args();
        foreach ($args as $arg)
        {
            preg_match('/^([-+?])([a-zA-Z0-9_]+)$/', $arg, $m);
            if ($m)
            {
                $direction = $m[1];
                $field = $m[2];
            }
            else
            {
                $direction = '+';
                $field = $arg;
            }
            $this->_sorting[] = array($field, $direction);
        }
        return $this;
    }

    function limit($a, $b=0)
    {
        if ($b)
        {
            $this->_start = $a;
            $this->_num = $b;
        }
        else
        {
            $this->_start = 0;
            $this->_num = $a;
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

    function count()
    {
        if (is_null($this->_count))
        {
            list($query, $params) = $this->build_count_query();
            $s = $this->execute_query($query, $params);
            $row = $s->fetch();
            if (!($this->_count = @$row['_total']))
            {
                $this->_count = 0;
            }
        }
        return $this->_count;
    }

    function _fill_cache()
    {
        list($query, $params) = $this->build_query();
        $s = $this->execute_query($query, $params);
        $this->_cache = $this->_results_to_objects($s);
    }

    var $_max_existing = array();

    function _disambiguate_params($params, $fparams, $fquery)
    {
        $fkeys = array_keys($fparams);
        $pkeys = array_keys($params);
        foreach ($fkeys as &$key)
        {
            if (in_array($key, $pkeys))
            {
                if (!array_key_exists($key, $this->_max_existing))
                {
                    $this->_max_existing[$key] = 0;
                }
                $this->_max_existing[$key]++;
                $new_key = "{$key}{$this->_max_existing[$key]}";
                $fquery = str_replace($key, $new_key, $fquery);
                $fparams[$new_key] = $fparams[$key];
                unset($fparams[$key]);
            }
        }
        return array($fquery, $fparams);
    }

    function build_count_query()
    {
        return $this->build_query(True);
    }

    function build_query($count=False)
    {
        // intended to be overridden to allow use of alternative backends
        $query = array();
        $params = array();
        $this->_max_existing = array();
        foreach ($this->_filters as &$filter)
        {
            // TODO - hide this from the developer
            list($fquery, $fparams) = $this->_disambiguate_params($params,
                $filter->params(), $filter->to_string());
            $query[] = $fquery;
            $params = array_merge($params, $fparams);
        }
        // TODO - extend to allow OR
        $query = join(' AND ', $query);
        $fields = '*';
        if ($count)
        {
            $fields = 'COUNT(*) AS _total';
        }
        $sql = <<<SQL
            SELECT {$fields}
            FROM {$this->_table}
SQL;
        if ($query)
        {
            $sql .= <<<SQL
            WHERE {$query}
SQL;
        }
        if (!$count)
        {
            $sorting = array();
            foreach ($this->_sorting as $order_by)
            {
                list($field, $direction) = $order_by;
                if ($direction == '+')
                {
                    $direction = 'ASC';
                }
                if ($direction == '-')
                {
                    $direction = 'DESC';
                }
                // TODO - implement random sort
                $sorting[] = "$field $direction";
            }
            if ($sorting)
            {
                $sorting = join(', ', $sorting);
                $sql .= <<<SQL
                ORDER BY {$sorting}
SQL;
            }
            if ($this->_num)
            {
                $sql .= ' LIMIT ';
                if ($this->_start)
                {
                    $sql .= "{$this->_start}, ";
                }
                $sql .= $this->_num;
            }
        }
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        return array($sql, $params);
    }

    function execute_query($query, $params)
    {
        // intended to be overridden to allow use of alternative backends
        $s = minim()->db()->prepare($query);
        $s->execute($params);
        return $s;
    }

    function _results_to_objects($s)
    {
        $objects = array();
        $model =& breve($this->_model);
        foreach ($s->fetchAll() as $row)
        {
            $objects[] =& $model->from($row);
        }
        return $objects;
    }

    function __get($name)
    {
        if ($name == 'items')
        {
            if (!$this->_cache)
            {
                $this->_fill_cache();
            }
            return $this->_cache;
        }
        // reveal intent
        if ($name == 'first')
        {
            if (!$this->_cache)
            {
                $this->_fill_cache();
            }
            if (sizeof($this->_cache) < 1)
            {
                return NULL;
            }
            return $this->_cache[0];
        }
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
            'range',
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
            'range' => '%s BETWEEN :%s AND :%s',
        );
        if ($this->operator == 'range')
        {
            return sprintf($ops[$this->operator], $this->field, 'from', 'to');
        }
        return str_replace('%s', $this->field, $ops[$this->operator]);
    }

    function params()
    {
        if ($this->operator == 'range' and is_array($this->value))
        {
            // this is a range value
            return array(":from" => $this->value[0],
                         ":to" => $this->value[1]);
        }
        return array(":{$this->field}" => $this->value);
    }
}
