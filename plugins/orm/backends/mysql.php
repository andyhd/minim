<?php
class Minim_Orm_MySQL_Backend implements Minim_Orm_Backend
{
    var $_orm;
    var $_db;

    function __construct($params, &$orm) // {{{
    {
        $this->_orm = $orm;
        $dsn = '';
        if (!(array_key_exists('user', $params) and
              array_key_exists('dbname', $params)))
        {
            if (array_key_exists('host', $params))
            {
                $port = (array_key_exists('port', $params)) ?
                    ";port={$params['port']}" : "";
                $dsn = "mysql:host={$param['host']}$port";
            }
            elseif (array_key_exists('unix_socket', $params))
            {
                $dsn = "mysql:unix_socket={$params['unix_socket']}";
            }
        }
        $this->_db = new PDO("mysql:$dsn;dbname={$params['dbname']}");
        $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } // }}}

    function save(&$do) // {{{
    {
        $fields = array_keys($this->_fields);
        $values = preg_replace('/^/', ':', $fields);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $this->_db_table, join(',', $fields), join(',', $values));
        $sth = $_db->prepare($sql);
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

    function build_count_query() // {{{
    {
        return $this->build_query(True);
    } // }}}

    function build_query($count=False) // {{{
    {
        return $this->_manager->_build_query($this);

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
    } // }}}

    function execute_query($query, $params) // {{{
    {
        // intended to be overridden to allow use of alternative backends
        $s = minim('db')->prepare($query);
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
}
