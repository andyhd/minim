<?php
class Minim_Orm_Sqlite_Backend implements Minim_Orm_Backend // {{{
{
    var $_orm;
    var $_db;
    var $_max_existing = array();

    function __construct($params, &$orm) // {{{
    {
        $this->_orm = $orm;
        if (!array_key_exists('database', $params))
        {
            throw new Minim_Orm_Exception(
                "Sqlite backend requires 'database' parameter");
        }
        $this->_database = $params['database'];
        $this->_db =& new PDO("sqlite:{$this->_database}");
        $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } // }}}

    function save(&$do, &$manager) // {{{
    {
        $fields = array_keys($manager->_fields);
        $values = preg_replace('/^/', ':', $fields);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $manager->_db_table, join(',', $fields), join(',', $values));
        $sth = $this->_db->prepare($sql);
        $values = array_combine($values, array_values($do->_data));
        $sth->execute($values);
    } // }}}

    function &get($params, &$manager) // {{{
    {
        $criteria = '';
        foreach ($params as $key => $value)
        {
            if (strlen($criteria) > 0)
            {
                $criteria .= ' AND ';
            }
            $criteria .= "$key = :$key";
        }
        $sql = sprintf('SELECT * FROM %s WHERE %s',
            $manager->_db_table, $criteria);
        $sth = $this->_db->prepare($sql);
        $values = array_combine(
            preg_replace('/^/', ':', array_keys($params)),
            array_values($params)
        );
        $sth->execute($values);
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        $num_results = count($results);
        if ($num_results == 1)
        {
            $instance =& $manager->create($results[0]);
            $instance->_in_db = TRUE;
            return $instance;
        }
        elseif ($num_results > 1)
        {
            throw new Minim_Orm_Exception("More than one result for get");
        }
        throw new Minim_Orm_Exception("No results for get");
    } // }}}

    function &get_dataobjects(&$modelset) // {{{
    {
        list($query, $params) = $this->build_query($modelset);
        $s =& $this->execute_query($query, $params);
        $objects = array();
        $manager =& $modelset->_manager;
        foreach ($s->fetchAll() as $row)
        {
            $objects[] =& $manager->create($row);
        }
        return $objects;
    } // }}}

    function count_dataobjects(&$modelset) // {{{
    {
        list($query, $params) = $this->build_count_query($modelset);
        $s =& $this->execute_query($query, $params);
        $row = $s->fetch();
        if (!($count = @$row['_total']))
        {
            $count = 0;
        }
        return $count;
    } // }}}

    function _disambiguate_params($params, $fparams, $fquery) // {{{
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
    } // }}}

    function build_count_query(&$modelset) // {{{
    {
        return $this->build_query($modelset, True);
    } // }}}

    function build_query(&$modelset, $count=False) // {{{
    {
        $query = array();
        $params = array();
        $this->_max_existing = array();
        foreach ($modelset->_filters as &$filter)
        {
            // TODO - hide this from the developer
            list($expr, $value) = $this->render($filter);
            $query[] = $expr;
            $params = array_merge($params, $value);
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
            FROM {$modelset->_manager->_db_table}
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
            foreach ($this->_manager->_sorting as $order_by)
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
            if ($modelset->_num)
            {
                $sql .= ' LIMIT ';
                if ($modelset->_start)
                {
                    $sql .= "{$modelset->_start}, ";
                }
                $sql .= $modelset->_num;
            }
        }
        $sql = trim(preg_replace('/\s+/', ' ', $sql));

        $ret = array($sql, $params);
        error_log(print_r($ret, TRUE));

        return $ret;
    } // }}}

    function execute_query($query, $params) // {{{
    {
        // intended to be overridden to allow use of alternative backends
        $s = $this->_db->prepare($query);
        $s->execute($params);
        return $s;
    } // }}} 

    function render(&$filter) // {{{
    {
        switch ($filter->_operator)
        {
            case '=':
            case '>':
            case '<':
            case '>=':
            case '<=':
                $placeholder = $filter->_field.'_'.substr(md5(microtime()), -4);
                return array(
                    "{$filter->_field} {$filter->_operator} :$placeholder",
                    array($placeholder => $filter->_operand)
                );
            case '!=':
                $placeholder = $filter->_field.'_'.substr(md5(microtime()), -4);
                return array(
                    "NOT ({$filter->_field} = :$placeholder)",
                    array($placeholder => $filter->_operand)
                );
            case 'range':
                $from = $filter->_field.'_from_'.substr(md5(microtime()), -4);
                $to = $filter->_field.'_to_'.substr(md5(microtime()), -4);
                return array(
                    "{$filter->_field} BETWEEN :$from AND :$to",
                    array(
                        $from => $filter->_operand[0],
                        $to => $filter->_operand[1]
                    )
                );
        }
        return array('', array());
    } // }}}
} // }}}
