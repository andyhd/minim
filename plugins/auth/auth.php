<?php
class Minim_Auth implements Minim_Plugin
{
    var $_backend;
    var $backend_paths;

    function Minim_Auth()
    {
        $this->_backend = NULL;
        $this->backend_paths = array(
            realpath(join(DIRECTORY_SEPARATOR, array(
                dirname(__FILE__), 'backends'
            )))
        );
    }

    /**
     * Set the backend for authentication, eg: LDAP, ORM, etc 
     * Return FALSE on failure
     */
    function &set_backend($type, $params=array())
    {
        // do not load a second backend
        if ($this->_backend)
        {
            return TRUE;
        }

        $pattern = '/^class[\W]+(\w+)\W+implements[^\{]*Minim_Auth_Backend/ms';
        foreach ($this->backend_paths as $path)
        {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file)
            {
                $filename = $file->getPathname();
                if (substr($filename, -4) == '.php'
                    and $type == basename($filename, '.php')
                    and preg_match($pattern, file_get_contents($filename), $m))
                {
                    require_once $filename;

                    $class = $m[1];

                    $this->_backend = new $class($params, $this);

                    // break out of loops
                    return $this->_backend;
                }
            }
        }
        throw new Minim_Auth_Backend_Exception(
            "Auth backend $type not found");
    }

    function login($username, $password)
    {
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        return $this->_backend->login($username, $password);
    }

    function logout($user)
    {
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        return $this->_backend->logout($user);
    }

    function logged_in($user)
    {
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        return $this->_backend->logged_in($user);
    }

    function allow($user, $action)
    {
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        return $this->_backend->allow($user, $action);
    }
}

class Minim_User
{
    var $_auth;
    var $username;
    var $password;

    function Minim_User($username, $password, &$auth)
    {
        $this->username = $username;
        $this->password = $password;
        $this->_auth =& $auth;
    }

    function can($action)
    {
        return $this->_auth->allow($this, $action);
    }

    function logout()
    {
        $this->_auth->logout($this);
    }

    function logged_in()
    {
        return $this->_auth->logged_in($this);
    }
}

class Minim_Auth_Exception extends Exception {}

class Minim_Auth_Backend_Exception extends Exception {}

interface Minim_Auth_Backend {}
