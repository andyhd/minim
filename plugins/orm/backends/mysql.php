<?php
class Minim_Orm_MySQL_Backend implements Minim_Orm_Backend
{
    var $_orm;
    var $_db;

    function __construct(&$orm) // {{{
    {
        $this->_orm = $orm;
        $this->_db = NULL;
    } // }}}

    function _get_connection() // {{{
    {
        $this->_db = new PDO("mysql:host
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

    function _results_to_objects($s) // {{{
    {
        $objects = array();
        $model =& minim('orm')->{$this->_model};
        foreach ($s->fetchAll() as $row)
        {
            $objects[] =& $model->from($row);
        }
        return $objects;
    } // }}}
}
