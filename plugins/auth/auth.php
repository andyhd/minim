<?php
require_once 'minim/lib/rc4.php';

class Minim_Auth implements Minim_Plugin
{
    var $_backend;
    var $backend_paths;
    var $encryption_key;
    var $plugin_path;

    function Minim_Auth()
    {
        $this->_backend = NULL;
        $this->backend_paths = array(
            realpath(join(DIRECTORY_SEPARATOR, array(
                dirname(__FILE__), 'backends'
            )))
        );
        $this->encryption_key = 'Ch4Ng3_M3';
        $this->plugin_path = dirname(__FILE__);
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
        // check session cookie
        if ($user = $this->get_logged_in_user())
        {
            // don't bother logging in
            return $user;
        }
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        $user = $this->_backend->login($username, $password);
        if ($user)
        {
            // set user cookie
            $ts = date('YmdHis', $_SERVER['REQUEST_TIME']);
            $hash = md5("uid:{$user->id},ts:$ts");
            $plain = "user={$user->id}&timestamp=$ts&hash=$hash";
            error_log("Setting user cookie: $plain"); 
            $_COOKIE['u'] = $this->encrypt($plain);;
            return $user;
        }
        return NULL;
    }

    function logout($user)
    {
        if (!$this->logged_in($user))
        {
            throw new Minim_Auth_Exception("User {$user->username} not logged in");
        }
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        return $this->_backend->logout($user);
    }

    function logged_in($user)
    {
        return $user == $this->get_logged_in_user();
    }

    function allow($user, $action)
    {
        if (!$this->_backend)
        {
            throw new Minim_Auth_Backend_Exception("Auth backend not set");
        }
        return $this->_backend->allow($user, $action);
    }

    function get_logged_in_user()
    {
        if (array_key_exists('u', $_COOKIE))
        {
            error_log("User cookie: {$_COOKIE['u']}");
            $cookie = $this->decrypt($_COOKIE['u']);
            // XXX - this could inject random variables into the current scope
            $vars = array();
            parse_str($cookie, $vars);

            // check hash matches user_id and request time
            $check = md5("uid:{$vars['user']},ts:{$vars['timestamp']}");
            if ($vars['hash'] == $check)
            {
                // user is logged in
                error_log("User cookie is valid");
                return $this->_backend->get_user($vars['user']);
            }
        }
        return NULL;
    }

    function encrypt($plaintext)
    {
        return base64_encode(rc4($plaintext, $this->encryption_key));
    }

    function decrypt($ciphertext)
    {
        return rc4(base64_decode($ciphertext), $this->encryption_key);
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
