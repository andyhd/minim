<?php
class Minim_Router implements Minim_Plugin
{
    var $_routes;
    var $_views;
    var $not_found;

    function Minim_Router() // {{{
    {
        $this->_routes = array();
        $this->view_paths = array();
        $this->not_found = new Minim_Route($this, '');
    } // }}}

    /**
     * Find the view file matching the specified name
     */
    function get_view($view)
    {
        foreach ($this->view_paths as $path)
        {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file)
            {
                if (basename($file) == "$view.php")
                {
                    return $file->getPathname();
                }
            }
        }
        return NULL;
    }

    /**
     * Register a URL pattern to match against
     */
    function &url($url_pattern) // {{{
    {
        $route = new Minim_Route($this, $url_pattern);
        $this->_routes[] =& $route;
        return $route;
    } // }}}

    /**
     * Given a URL, find a matching route
     */
    function &resolve($url) // {{{
    {
        foreach ($this->_routes as &$route)
        {
            if (preg_match("/{$route->url_pattern}/", $url))
            {
                return $route;
            }
        }
        return $this->not_found;
    } // }}}

    /**
     * Build a URL for the specified view (and action)
     */
    function url_for($view, $action=NULL) // {{{
    {
        foreach ($this->_routes as $route)
        {
            if ($route->name == $view and $route->action == $action)
            {
                return $route->url_pattern;
            }
        }
        throw new Minim_Router_Exception("View $view not found");
    } // }}}
}

class Minim_Route
{
    var $name;
    var $url_pattern;
    var $view;
    var $action;
    var $_router;

    function Minim_Route(&$router, $url_pattern) // {{{
    {
        $this->_router =& $router;
        $this->url_pattern = $url_pattern;
        $this->name = NULL;
        $this->view = NULL;
        $this->action = NULL;
    } // }}}

    /**
     * Map route to a view (plus optional action)
     */
    function &maps_to($view, $action=NULL) // {{{
    {
        $this->name = $view;
        $_view = $this->_router->get_view($view);
        if ($_view == NULL)
        {
            throw new Minim_Router_Exception("View $view not found");
        }
        $this->view = $_view;
        $this->action = $action;
        return $this;
    } // }}}
}

class Minim_Router_Exception extends Exception {}
