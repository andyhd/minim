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
            if (preg_match("`{$route->url_pattern}`", $url, $params))
            {
                $route->params = $params;
                return $route;
            }
        }
        return $this->not_found;
    } // }}}

    /**
     * Build a URL for the specified view (and action)
     */
    function url_for($_view, $_params=array()) // {{{
    {
        foreach ($this->_routes as $_route)
        {
            if ($_route->name == $_view)
            {
                extract($_params);
                $_url = $_route->url_pattern;

                // find required params
                preg_match_all(',\(\?P<(.*?)>.*?\),', $_url, $_m);

                // make sure values were passed in
                foreach ($_m[1] as $_k)
                {
                    if (!array_key_exists($_k, $_params))
                    {
                        throw new Minim_Router_Exception(
                            "Route {$_route->name} requires $_k parameter");
                    }

                    // don't append this one to the query string
                    unset($_params[$_k]);
                }

                // replace optional params with their values, or remove them
                $_url = preg_replace(',\(\?\:/\(\?P<(.*?)>.*?\)\)\?,e',
                    'isset($$1) ? "/{$$1}" : ""', $_url);

                // replace named params with their values
                $_url = preg_replace(',\(\?P<(.*?)>.*?\),e', '$$1', $_url);

                // remove start and end anchors
                $_url = ltrim(rtrim($_url, '/$'), '^');

                // append extra params as query string
                if ($_params)
                {
                    $_url .= '?'.http_build_query($_params);
                }

                return $_url;
            }
        }
        throw new Minim_Router_Exception("View $_view not found");
    } // }}}

    /**
     * Pass control to the view indicated by the request
     */
    function route_request() // {{{
    {
        // parse request URI
        $parts = @parse_url($_SERVER['REQUEST_URI']);

        if ($parts)
        {
            $path = $parts['path'];
            if (strpos($path, $this->webroot) === 0)
            {
                $path = substr($path, strlen($this->webroot));
            }
        }
        else
        {
            $path = '/';
        }

        // resolve URL
        $route =& $this->resolve($path);
        if ($route->view)
        {
            error_log('URI matches route '.print_r(array(
                'name' => $route->name,
                'url_pattern' => $route->url_pattern,
                'view' => $route->view,
                'params' => $route->params
            ), TRUE));
            if ($route->params)
            {
                $_GET = array_merge($_GET, $route->params);
                $_REQUEST = array_merge($_REQUEST, $route->params);
            }
            require_once($route->view);
            return;
        }

        // 404
        header('HTTP/1.1 404 Not Found');
        // TODO
    } // }}}
}

class Minim_Route
{
    var $name;
    var $url_pattern;
    var $view;
    var $params;
    var $_router;

    function Minim_Route(&$router, $url_pattern) // {{{
    {
        $this->_router =& $router;
        $this->url_pattern = $url_pattern;
        $this->name = NULL;
        $this->view = NULL;
        $this->params = NULL;
    } // }}}

    /**
     * Map route to a view
     */
    function &maps_to($view) // {{{
    {
        $this->name = $view;
        $_view = $this->_router->get_view($view);
        if ($_view == NULL)
        {
            throw new Minim_Router_Exception("View $view not found");
        }
        $this->view = $_view;

        return $this->_router;
    } // }}}
}

class Minim_Router_Exception extends Exception {}
