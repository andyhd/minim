<?php
class Minim_Routing implements Minim_Plugin
{
    // routing methods
    var $_url_map;
    var $webroot;

    function Minim_Routing() // {{{
    {
        $this->_url_map = array();
        $this->webroot = '/';
    } // }}}

    function &_url_map_for($view, $action) // {{{
    {
        foreach ($this->_url_map as &$map)
        {
            if ($map['view'] == $view and $map['action'] == $action)
            {
                return $map;
            }
        }
        $nullvar = null;
        return $nullvar;
    } // }}}

    function map_url($url_pattern, $view, $action=NULL, $alt_path=NULL) // {{{
    {
        $map =& $this->_url_map_for($view, $action);
        if (is_null($map))
        {
            $this->_url_map[] = array(
                'url_pattern' => $url_pattern,
                'view' => $view,
                'action' => $action,
                'alt_path' => $alt_path
            );
        }
        else
        {
            // replace existing map
            $map = array(
                'url_pattern' => $url_pattern,
                'view' => $view,
                'action' => $action,
                'alt_path' => $alt_path
            );
            error_log("Replacing URL map for $view:$action");
        }
        return $this;
    } // }}}

    function url_for($_mapping, $_params=array()) // {{{
    {
        @list($_view, $_action) = explode(':', $_mapping);
        if (is_null($_action))
        {
            $_view = $_mapping;
        }
        $_map = $this->_url_map_for($_view, $_action);

        if ($_map)
        {
            error_log("Using URL map: $_mapping -> ".print_r($_map, TRUE));
            error_log("Params: ".print_r($_params, TRUE));
            extract($_params);
            $_pat = $_map['url_pattern'];
            # replace optional params first
            preg_match_all(',\(\?P<(.*?)>.*?\),', $_pat, $_m);
            foreach ($_m[1] as $_k)
            {
                if (array_key_exists($_k, $_params))
                {
                    unset($_params[$_k]);
                }
            }
            $_rev = preg_replace(',\(\?\:/\(\?P<(.*?)>.*?\)\)\?,e',
                'isset($$1) ? "/{$$1}" : ""', $_pat);
            $_rev = preg_replace(',\(\?P<(.*?)>.*?\),e', '$$1', $_rev);
            $_rev = ltrim(rtrim($_rev, '/$'), '^');
            $_rev = $this->webroot.$_rev;
            if ($_params)
            {
                $_rev .= '?'.http_build_query($_params); 
            }
            error_log("Mapped to URL: $_rev");
            return $_rev;
        }
        throw new Minim_Routing_Exception(
            "URL Mapping not found: $_mapping");
    } // }}}

    function resolve($url) // {{{
    {
        // apply url_map patterns in order until match found
        foreach ($this->_url_map as $map)
        {
            extract($map);
            if (preg_match(','.$url_pattern.',', $url, $params))
            {
                error_log('Found URL map: '.print_r($map, TRUE).
                                    print_r($params, TRUE));
                // found a match, return actual path and params
                $path = "views/{$view}.php";
                if (isset($alt_path))
                {
                    $path = $alt_path;
                }
                // append action to params
                $params['action'] = $action;
                return array($path, $params);
            }
        }
        return array(FALSE, FALSE);
    } // }}}

    function redirect($page, $params=array()) // {{{
    {
        header('Location: '.$this->url_for($page, $params));
        exit;
    } // }}}

    function mod_rewrite_rules($base=NULL) // {{{
    {
        $rules = array();
        if ($base)
        {
            $rules[] = "RewriteBase $base";
        }
        foreach ($this->_url_map as $map)
        {
            extract($map);
            if ($base)
            {
                $url_pattern = preg_replace(',^\^/,', '^', $url_pattern);
            }
            $path = "views/{$view}.php";
            if ($alt_path)
            {
                $path = $alt_path;
            }
            $rule = "RewriteRule {$url_pattern} {$path}";
            $params = array();
            if (preg_match_all(',\(\?P<(.*?)>.*?\),', $url_pattern, $m))
            {
                foreach ($m[1] as $i => $param)
                {
                    // mod_rewrite doesn't do named params :(
                    $params[] = "$param=$".($i + 1);
                }
                $rule .= "?".join('&', $params);
            }
            if ($action)
            {
                $prefix = $params ? '&' : '?';
                $rule .= "{$prefix}action=$action";
            }
            $rules[] = "$rule [QSA,L]";
        }
        return $rules;
    } // }}}

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
        list($path, $params) = $this->resolve($path);
        if (is_readable($path))
        {
            if ($params)
            {
                $_GET = array_merge($_GET, $params);
                $_REQUEST = array_merge($_REQUEST, $params);
            }
            require_once($path);
            return;
        }

        // 404
        header('HTTP/1.1 404 Not Found');
        minim('templates')->render_404();
        exit;
    } // }}}
}

class Minim_Routing_Exception extends Exception {}
