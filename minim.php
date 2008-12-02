<?php
/**
 * Syntax sugar function - provides interface to singleton instances of Minim
 * class and lazy loaded plugins.
 */
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

/**
 * Provides plugin management and utility methods
 */
class Minim
{
    var $root;
    var $webroot;

    // constructor
    function Minim() // {{{
    {
        $this->root = realpath(dirname(__FILE__));
        $this->webroot = dirname($_SERVER['SCRIPT_NAME']);
        $this->isXhrRequest = strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) ==
                              'xmlhttprequest';
        if (!defined('STDOUT'))
        {
            session_start();
        }
        // cache user messages so we don't erase new ones in the render phase
        $this->get_plugin('user_messaging')->get_messages();
    } // }}}

    // plugin methods
    var $_plugins = NULL;

    function _init_plugins() // {{{
    {
        if ($this->_plugins === NULL)
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
            error_log("Plugins available: ".
                print_r(array_keys($this->_plugins), TRUE));
        }
    } // }}}

    function &get_plugin($plugin) // {{{
    {
        if ($this->_plugins === NULL)
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

    function register_plugin($dir) // {{{
    {
        if ($this->_plugins === NULL)
        {
            $this->_init_plugins();
        }
        if (is_dir($dir))
        {
            // search for plugin class
            $pat = '/class\s+([^\s]+)\s+implements\s+Minim_Plugin/m';
            $plugin = strtolower(basename($dir));

            $dh = opendir($dir);
            while ($file = readdir($dh))
            {
                if (substr($file, -4) != '.php')
                {
                    continue;
                }
                $contents = file_get_contents("$dir/$file");

                // check for Minim_Plugin implementors
                if (preg_match_all($pat, $contents, $m))
                {
                    foreach ($m[1] as $class)
                    {
                        $this->_plugins[$plugin] = array(
                            'file' => "$dir/$file",
                            'class' => $class
                        );
                    }
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

    function front_controller() // {{{
    {
        $page = 'home';
        $action = '';

        // parse request URI
        $parts = @parse_url($_SERVER['REQUEST_URI']);
        if ($parts)
        {
            $path = str_replace($this->webroot, '', $parts['path']);

            // resolve URL
            list($path, $params) = minim('routing')->resolve($path);
            if (is_readable($path))
            {
                if ($params)
                {
                    $_GET = array_merge($_GET, $params);
                    $_REQUEST = array_merge($_REQUEST, $params);
                }
                require_once($path);
            }
        }
    } // }}}

    function find($pattern, $path_list) // {{{
    {
        foreach ($path_list as $path)
        {
            $matches = glob(join(DIRECTORY_SEPARATOR, array($path, $pattern)));
            if ($matches)
            {
                return $matches;
            }
        }
        return FALSE;
    } // }}}

    function grep($pattern, $path_list) // {{{
    {
        $matches = array();
        foreach ($path_list as $path)
        {
            if ($dh = opendir($path))
            {
                while ($dl = readdir($dh))
                {
                    if ($contents = file_get_contents("$path/$dl"))
                    {
                        if (preg_match_all($pattern, $contents, $m))
                        {
                            $matches[] = array(
                                'file' => "$path/$dl",
                                'matches' => $m
                            );
                        }
                    }
                }
            }
        }
        return $matches;
    } // }}}
}

interface Minim_Plugin {}
