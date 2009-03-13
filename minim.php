<?php
/**
 * Syntax sugar function - provides interface to singleton instances of Minim
 * class and lazy loaded plugins.
 * @param string $plugin (optional) Name of plugin to access
 * @return Reference to singleton instance of Minim class or specified plugin.
 */
function &minim($plugin=NULL)
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
}

/**
 * Concatenates parameters into a path string and converts to an absolute path.
 * @return string Absolute path or FALSE on failure
 */
function build_path()
{
    $args = func_get_args();
    $path = realpath(join(DIRECTORY_SEPARATOR, $args));
    if ($path)
    {
        return $path;
    }
    return FALSE;
}

/**
 * Convenience method to determine if this script was initiated by an
 * XMLHTTPRequest (AJAX)
 * @return boolean TRUE if called by AJAX
 */
function is_ajax()
{
    return strtolower(@$_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Provides plugin management and utility methods
 */
class Minim
{
    var $plugin_paths;
    var $_plugins;

    // constructor
    function Minim()
    {
        $this->_plugins = array();
        $this->plugin_paths = array(
            // add default plugin path
            build_path(dirname(__FILE__), 'plugins')
        );
    }

    /**
     * Search plugin paths for a named plugin. Updates $this->_plugins if found.
     * @param string $name Plugin name
     * @return boolean TRUE if plugin found
     */
    function _find_plugin($name)
    {
        // validate the plugin name
        if (!$name)
        {
            return FALSE;
        }
        $key = strtolower($name);

        // plugins implement Minim_Plugin interface, so search for that
        $pat = '/class\s+([^\s]+)\s+implements\s+Minim_Plugin/m';

        // list files already checked, so we can skip them
        $already_checked = array();
        foreach ($this->_plugins as $plugin)
        {
            // don't care about duplicates
            $already_checked[] = $plugin['file'];
        }

        foreach ($this->plugin_paths as $path)
        {
            $dir = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path));
            foreach ($dir as $file)
            {
                $filename = $file->getPathname();
                if (substr($filename, -4) == '.php'
                    and basename($filename, '.php') == $key
                    // don't waste time opening files we already checked
                    and !in_array($filename, $already_checked)
                    and preg_match($pat, file_get_contents($filename), $m))
                {
                    // store details of the plugin as we will only instantiate
                    // on demand
                    $this->_plugins[$key] = array(
                        'file' => $filename,
                        'class' => $m[1]
                    );

                    // don't waste time checking any more files
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    /**
     * Return a reference to the named plugin singleton instance.
     * @param string $name Name of the plugin to access
     * @return reference to plugin object
     * @throws Minim_Exception if named plugin not found
     */
    function &get_plugin($name)
    {
        // if we already have details of the plugin, fine
        // otherwise, search plugin_paths for it
        $key = strtolower($name);
        if (array_key_exists($key, $this->_plugins)
            or $this->_find_plugin($name))
        {
            // only load and instantiate if we don't already have an instance
            // this enforces singleton behaviour
            $plugin =& $this->_plugins[$key];
            if (!array_key_exists('instance', $plugin))
            {
                // load and instantiate the plugin
                $included = include_once($plugin['file']);
                if ($included)
                {
                    // keep a reference to the plugin singleton instance
                    $plugin['instance'] = new $plugin['class'];
                }
                else
                {
                    throw new Minim_Exception("Failed loading $name plugin");
                }
            }
            return $plugin['instance'];
        }

        // if there were any problems, we fall through to here
        throw new Minim_Exception(
            "Plugin $name not found: ".
            print_r(array_keys($this->_plugins), TRUE)
        );
    }

    // TODO - factor this out into auth plugin
    function user()
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
    }
}

interface Minim_Plugin {}

class Minim_Exception extends Exception {}
