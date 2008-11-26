<?php
class Minim_Database implements Minim_Plugin
{
    var $_host;
    var $_port;
    var $_socket;
    var $_user;
    var $_password;
    var $_name;
    var $_dbh;

    function Minim_Database() // {{{
    {
    } // }}}

    function &get_connection() // {{{
    {
        if (!$this->_dbh)
        {
            $dsn = "mysql:dbname={$this->_name};host={$this->_host}";
            if (isset($this->_socket))
            {
                $dsn .= ";unix_socket={$this->_socket}";
            }
            if (class_exists('PDO'))
            {
                try
                {
                    $this->_dbh =& new PDO($dsn, $this->_user, $this->_password);
                }
                catch (PDOException $e)
                {
                    error_log("PDO error: ".$e->getMessage());
                }
            }
            if (!$this->_dbh and function_exists('mysql_connect'))
            {
                error_log("Using FakePDO");
                require_once minim()->lib('FakePDO.class');
                $this->_dbh =& new FakePDO($dsn, $this->_user, $this->_password);
            }
            if (!$this->_dbh)
            {
                die('Failed to connect to DB');
            }
        }
        return $this->_dbh;
    } // }}}

    function &prepare($sql) // {{{
    {
        $conn = $this->get_connection();
        $sth = $conn->prepare($sql);
        return $sth;
    } // }}}

    function &host($host=NULL) // {{{
    {
        if ($host)
        {
            $this->_host = $host;
            return $this;
        }
        return $this->_host;
    } // }}}

    function &socket($socket=NULL) // {{{
    {
        if ($socket)
        {
            $this->_socket = $socket;
            return $this;
        }
        return $this->_socket;
    } // }}}

    function &user($user=NULL) // {{{
    {
        if ($user)
        {
            $this->_user = $user;
            return $this;
        }
        return $this->_user;
    } // }}}

    function &password($password=NULL) // {{{
    {
        if ($password !== NULL)
        {
            $this->_password = $password;
            return $this;
        }
        return $this->_password;
    } // }}}

    function &name($name=NULL) // {{{
    {
        if ($name)
        {
            $this->_name = $name;
            return $this;
        }
        return $this->_name;
    } // }}}
}
