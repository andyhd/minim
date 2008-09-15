<?php
class Minim_Database implements Minim_Plugin
{
    var $_host;
    var $_port;
    var $_socket;
    var $_user;
    var $_password;
    var $_name;

    function Minim_Database() // {{{
    {
    } // }}}

    function get_connection() // {{{
    {
        static $dbh;
        if (!$dbh)
        {
            $dsn = "mysql:dbname={$this->_name};host={$this->_host}";
            if (isset($this->_socket))
            {
                $dsn .= ";unix_socket={$this->_socket}";
            }
            if (FALSE) //class_exists('PDO'))
            {
                try
                {
                    $dbh = new PDO($dsn, $this->_user, $this->_password);
                }
                catch (PDOException $e)
                {
                    die("Could not connect: ".$e->getMessage());
                }
            }
            else
            {
                require minim()->lib('FakePDO.class');
                $dbh = new FakePDO($dsn, $this->_user, $this->_password);
            }
        }
        return $dbh;
    } // }}}

    function host($host=NULL) // {{{
    {
        if ($host)
        {
            $this->_host = $host;
            return $this;
        }
        return $this->_host;
    } // }}}

    function socket($socket=NULL) // {{{
    {
        if ($socket)
        {
            $this->_socket = $socket;
            return $this;
        }
        return $this->_socket;
    } // }}}

    function user($user=NULL) // {{{
    {
        if ($user)
        {
            $this->_user = $user;
            return $this;
        }
        return $this->_user;
    } // }}}

    function password($password=NULL) // {{{
    {
        if ($password !== NULL)
        {
            $this->_password = $password;
            return $this;
        }
        return $this->_password;
    } // }}}

    function name($name=NULL) // {{{
    {
        if ($name)
        {
            $this->_name = $name;
            return $this;
        }
        return $this->_name;
    } // }}}
}
