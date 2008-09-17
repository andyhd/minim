<?php
class Minim_TemplateEngine implements Minim_Plugin
{
    // templating methods
    var $_blocks;
    var $_extends;

    function Minim_TemplateEngine() // {{{
    {
        $this->_blocks = array();
        $this->_extends = array();
    } // }}}

    function set_block($name, $contents) // {{{
    {
        minim('log')->debug("Setting $name block");
        $this->_blocks[$name] = $contents;
    } // }}}

    function get_block($name) // {{{
    {
        if (array_key_exists($name, $this->_blocks))
        {
            minim('log')->debug("Fetching $name block");
            return $this->_blocks[$name];
        }
        minim('log')->debug("Block $name not found");
        return "";
    } // }}}

    function extend($name) // {{{
    {
        minim('log')->debug("Extending $name template");
        array_push($this->_extends, $name);
    } // }}}

    function render($_name, $_context=array()) // {{{
    {
        minim('log')->debug("RENDER CALLED");
        $_root = minim()->root;
        ob_start();
        $_filename = "$_root/templates/$_name.php";
        if (is_readable($_filename))
        {
            minim('log')->debug("Rendering $_name template");
            minim('log')->debug("Context: " . print_r($_context, TRUE));
            extract($_context);
            include $_filename;
        }
        else
        {
            die("Template $_name not found at $_filename");
        }

        // render any parent templates
        if ($template = array_pop($this->_extends))
        {
            $this->render($template, $_context);
        }
        ob_end_flush();
    } // }}}

    function render_404() // {{{
    {
        $search_engines = array(
            'Ask Jeeves' => '\.ask\.co.*\bask=([^&]+)',
            'Google' => 'google\.co.*\bq=([^&]+)',
            'MSN' => 'msn\.co.*\bq=([^&]+)',
            'Yahoo!' => 'yahoo\.co.*\bp=([^&]+)',
        );
        $url = htmlspecialchars($_SERVER['REQUEST_URI']);
        if ($referrer = @$_SERVER['HTTP_REFERER'])
        {
            if (preg_match('/^http:[^\/]+pagezero/', $referrer))
            {
                $this->render('404-my-bad', array(
                    'url' => $url,
                ));
                return;
            }
            foreach ($search_engines as $name => $search_engine)
            {
                if (preg_match('/'.$search_engine.'/', $referrer, $m))
                {
                    $terms = htmlspecialchars(urldecode($m[1]));
                    $this->render('404-search', array(
                        'url' => $url,
                        'terms' => $terms,
                        'engine' => $name,
                    ));
                    return;
                }
            }
            $this->render('404-other-site', array(
                'url' => $url,
            ));
            return;
        }
        $this->render('404-no-search', array(
            'url' => $url,
        ));
    } // }}}

    function def_block($name) // {{{
    {
        ob_start();
    } // }}}

    function end_block($name) // {{{
    {
        $this->set_block($name, ob_get_clean());
    } // }}}

    function block($name) // {{{
    {
        echo $this->get_block($name);
    } // }}}
}
