<?php
class FakePDO
{
    var $dbh;
    var $resultset;

    function FakePDO($dsn, $user, $pass)
    {
        list($type, $params) = explode(':', $dsn);
        $host = $name = '';
        foreach (explode(';', $params) as $param)
        {
            list($key, $val) = explode('=', $param);
            $$key = $val;
        }
        if (isset($unix_socket))
        {
            $host .= ":$unix_socket";
        }
        $this->dbh = mysql_connect($host, $user, $pass);
        if (!$this->dbh)
        {
            die('FakePDO: Could not connect: '.mysql_error());
        }
        if (!mysql_select_db($dbname, $this->dbh))
        {
            die("FakePDO: Can't use $dbname: ".mysql_error());
        }
        $this->resultset = NULL;
    }

    function exec($sql)
    {
        mysql_query($sql, $this->dbh);
        return mysql_affected_rows($this->dbh);
    }

    function prepare($sql)
    {
        $stmt =& new FakePDOStatement($sql);
        $stmt->dbh =& $this->dbh;
        return $stmt;
    }
}

class FakePDOStatement
{
    var $sql;
    var $resultset;

    function FakePDOStatement($sql)
    {
        $this->sql = $sql;
        $this->resultset = NULL;
    }

    function execute($params=array())
    {
        foreach ($params as $key => &$val)
        {
            $val = mysql_real_escape_string($val, $this->dbh);
            if (!is_numeric($val))
            {
                $val = "'$val'";
            }
        }
        $sql = strtr($this->sql, $params);
        minim()->log("Executing query: $sql");
        $this->resultset = @mysql_query($sql, $this->dbh);
        if (!$this->resultset)
        {
            die('FakePDOStatement: Query failed: '.mysql_error($this->dbh));
        }
    }

    function fetch()
    {
        if (!$this->resultset)
        {
            return FALSE;
        }
        return @mysql_fetch_assoc($this->resultset);
    }

    function fetchAll()
    {
        @mysql_data_seek($this->resultset, 0);
        $results = array();
        while ($row = $this->fetch())
        {
            $results[] = $row;
        }
        minim()->log('Result set: '.var_export($results, TRUE));
        return $results;
    }
}
