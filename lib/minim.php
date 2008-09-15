<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_magic_quotes_runtime(0);

function &minim($plugin=NULL) // {{{
{
    static $instance;
    if (!$instance)
    {
        $instance = new Minim();
    }
    if (is_null($plugin))
    {
        return $instance;
    }
    return $instance->call_plugin($plugin);
} // }}}

class Minim_PluginProxy // {{{
{
    var $_plugin;

    function Minim_PluginProxy(&$plugin)
    {
        $this->_plugin =& $plugin;
    }

    function __call($method, $args)
    {
        $method = array(&$this->_plugin['instance'], $method);
        return call_user_func_array($method, $args);
    }

    function __get($name)
    {
        return $this->_plugin['instance']->$name;
    }
} // }}}

class Minim
{
    var $root;
    var $webroot;

    // constructor
    function Minim() // {{{
    {
        $this->root = realpath(dirname(__FILE__).'/../');
        $this->webroot = substr($_SERVER['PHP_SELF'], 0,
            strpos($_SERVER['PHP_SELF'], '/views'));
        $this->isXhrRequest = strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) ==
                              'xmlhttprequest';

        session_start();
        // cache user messages so we don't erase new ones in the render phase
        $this->user_messages();
    } // }}}

    // plugin methods
    var $_plugins;
    var $_plugins_initialized = FALSE;

    function init_plugins() // {{{
    {
        // prevent re-initialization
        if ($this->_plugins_initialized)
        {
            return $this->_plugins;
        }

        // get a list of available plugins
        $this->_plugins = array();
        $dh = opendir("{$this->root}/plugins");
        if (!$dh)
        {
            return NULL;
        }
        $pat = '/class\s+([^\s]+)\s+implements\s+Minim_Plugin/m';
        while ($dl = readdir($dh))
        {
            if (substr($dl, -4) == '.php')
            {
                $file = "$this->root/plugins/$dl";
                $plugin = strtolower(substr($dl, 0, -4));
                $contents = file_get_contents($file);

                # check for Minim_Plugin implementors
                if (preg_match_all($pat, $contents, $m))
                {
                    foreach ($m[1] as $class)
                    {
                        $this->_plugins[$plugin] = array(
                            'file' => $file,
                            'class' => $class
                        );
                    }
                }
            }
        }
        $this->_plugins_initialized = TRUE;
        $this->call_plugin('log')->debug("Plugins available: ".print_r(array_keys($this->_plugins), TRUE));
        return $this->_plugins;
    } // }}}

    function &get_plugin($plugin) // {{{
    {
        if (!$this->_plugins_initialized)
        {
            $this->init_plugins();
        }
        $key = strtolower($plugin);
        if (array_key_exists($key, $this->_plugins))
        {
            return $this->_plugins[$key];
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function &load_plugin($plugin) // {{{
    {
        $plugin =& $this->get_plugin($plugin);
        if ($plugin)
        {
            if (!array_key_exists('instance', $plugin))
            {
                require_once $plugin['file'];
                $plugin['instance'] =& new $plugin['class'];
            }
            return $plugin;
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function &call_plugin($plugin) // {{{
    {
        // pass a method call onto a plugin
        $plugin =& $this->load_plugin($plugin);
        if (!$plugin)
        {
            die("Plugin $plugin not found");
        }
        // return a plugin proxy object
        if (array_key_exists('instance', $plugin))
        {
            return $plugin['instance'];
        }

        $proxy =& new Minim_PluginProxy($plugin);
        return $proxy;
    } // }}}

    // path methods
    function template($name) // {{{
    {
        return "{$this->root}/templates/{$name}.php";
    } // }}}

    function lib($name) // {{{
    {
        return "{$this->root}/lib/{$name}.php";
    } // }}}

    function models($name) // {{{
    {
        return "{$this->root}/models/{$name}.php";
    } // }}}

    // session methods
    function user_message($msg) // {{{
    {
        if (!is_array(@$_SESSION['user_messages']))
        {
            $_SESSION['user_messages'] = array();
        }
        $_SESSION['user_messages'][] = $msg;
    } // }}}

    function user_messages() // {{{
    {
        static $messages;
        if (!$messages)
        {
            if (array_key_exists('user_messages', $_SESSION))
            {
                $messages = $_SESSION['user_messages'];
                unset($_SESSION['user_messages']);
            }
            else
            {
                $messages = array();
            }
        }
        return $messages;
    } // }}}

    function user() // {{{
    {
        static $user;
        if ($user === null)
        {
            $user = @$_SESSION['user'];
            if (!$user)
            {
                $user = false;
            }
        }
        return $user;
    } // }}}
}

interface Minim_Plugin // {{{
{
} // }}}
