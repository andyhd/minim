<?php
class Minim_Routing implements Minim_Plugin
{
    // routing methods
    var $_url_map;

    function Minim_Routing() // {{{
    {
        $this->_url_map = array();
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

    function map_url($url_pattern, $view, $action=null) // {{{
    {
        $map =& $this->_url_map_for($view, $action);
        if (is_null($map))
        {
            $this->_url_map[] = array(
                'url_pattern' => $url_pattern,
                'view' => $view,
                'action' => $action
            );
        }
        else
        {
            // replace existing map
            $map = array(
                'url_pattern' => $url_pattern,
                'view' => $view,
                'action' => $action
            );
            minim('log')->debug("Replacing URL map for $view:$action");
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
            minim('log')->debug("Using URL map: $_mapping -> ".print_r($_map, TRUE));
            minim('log')->debug("Params: ".print_r($_params, TRUE));
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
            $_rev = minim()->webroot.ltrim(rtrim($_rev, '/$'), '^');
            if ($_params)
            {
                $_rev .= '?'.http_build_query($_params); 
            }
            minim('log')->debug("Mapped to URL: $_rev");
            return $_rev;
        }
        return "#error:_mapping_not_found:_$_mapping";
    } // }}}

    function redirect($page, $params=array()) // {{{
    {
        header('Location: '.$this->url_for($page, $params));
        exit;
    } // }}}
}
