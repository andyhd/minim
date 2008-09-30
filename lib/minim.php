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
    return $instance->get_plugin($plugin);
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
        $this->get_plugin('user_messaging')->get_messages();
    } // }}}

    // plugin methods
    var $_plugins;

    function _init_plugins() // {{{
    {
        if (!$this->_plugins)
        {
            // get a list of available plugins
            $this->_plugins = array();
            $dh = opendir("{$this->root}/plugins");
            if (!$dh)
            {
                die("Plugins directory not found");
            }
            while ($dl = readdir($dh))
            {
                $this->register_plugin("{$this->root}/plugins/$dl");
            }
            $this->get_plugin('log')->debug("Plugins available: ".
                print_r(array_keys($this->_plugins), TRUE));
        }
    } // }}}

    function &get_plugin($plugin) // {{{
    {
        if (!$this->_plugins)
        {
            $this->_init_plugins();
        }
        $key = strtolower($plugin);
        if (array_key_exists($key, $this->_plugins))
        {
            $plugin =& $this->_plugins[$key];
            if (!array_key_exists('instance', $plugin))
            {
                require_once $plugin['file'];
                $plugin['instance'] =& new $plugin['class'];
            }
            return $plugin['instance'];
        }
        die("Plugin $plugin not found");
    } // }}}

    function register_plugin($file) // {{{
    {
        if (!$this->_plugins)
        {
            $this->_init_plugins();
        }
        if (substr($file, -4) == '.php')
        {
            $pat = '/class\s+([^\s]+)\s+implements\s+Minim_Plugin/m';
            $plugin = strtolower(substr(basename($file), 0, -4));
            $contents = file_get_contents($file);

            // check for Minim_Plugin implementors
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
        return $this;
    } // }}}

    // path methods
    function lib($name) // {{{
    {
        return "{$this->root}/lib/{$name}.php";
    } // }}}

    // session methods
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

interface Minim_Plugin {}
