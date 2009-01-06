<?php
class Minim_TemplateEngine
{
    var $_template_paths;
    var $_css_paths;
    var $_js_paths;
    var $_blocks;
    var $_extends;
    var $_def_stack;

    function Minim_TemplateEngine() // {{{
    {
        $this->_blocks = array();
        $this->_extends = array();
        $this->_def_stack = array();
        $this->_template_paths = array();
        $this->_css_paths = array();
        $this->_js_paths = array();
    } // }}}

    function _set_block($name, $contents) // {{{
    {
        error_log("Setting $name block");
        $this->_blocks[$name] = $contents;
    } // }}}

    function _get_block($name) // {{{
    {
        if (array_key_exists($name, $this->_blocks))
        {
            error_log("Fetching $name block");
            return $this->_blocks[$name];
        }
        error_log("Block $name not found");
        return "";
    } // }}}

    function extend($name) // {{{
    {
        error_log("Extending $name template");
        array_push($this->_extends, $name);
    } // }}}

    function append_path($path) // {{{
    {
        array_push($this->_template_paths, realpath($path));
    } // }}}

    function prepend_path($path) // {{{
    {
        array_unshift($this->_template_paths, realpath($path));
    } // }}}

    function append_css_path($path) // {{{
    {
        array_push($this->_css_paths, $path);
    } // }}}

    function append_js_path($path) // {{{
    {
        array_push($this->_js_paths, $path);
    } // }}}

    function render($_template, $_context=array()) // {{{
    {
        // find template by searching template path
        $_files = minim()->find("$_template.php", $this->_template_paths);
        if (!$_files)
        {
            error_log("$_template not found in template paths");
            return FALSE;
        }

        error_log("Rendering $_template from {$_files[0]}");
        error_log("Context: " . print_r($_context, TRUE));
        extract($_context);
        ob_start();
        include $_files[0];

        // render any parent templates
        if ($_parent = array_pop($this->_extends))
        {
            $this->render($_parent, $_context);
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

    function set($name) // {{{
    {
        array_push($this->_def_stack, $name);
        ob_start();
    } // }}}

    function end() // {{{
    {
        $name = array_pop($this->_def_stack);
        $this->_set_block($name, ob_get_clean());
    } // }}}

    function get($name) // {{{
    {
        echo $this->_get_block($name);
    } // }}}

    function include_css($name) // {{{
    {
        $files = minim()->find("$name.css", $this->_css_paths);
        if ($files)
        {
            echo <<<HTML
<link rel="stylesheet" type="text/css" href="{$files[0]}">
HTML;
        }
    } // }}}

    function include_js($name) // {{{
    {
        $files = minim()->find("$name.js", $this->_js_paths);
        if ($files)
        {
            echo <<<HTML
<script type="text/javascript" src="{$files[0]}"></script>
HTML;
        }
    } // }}}
}
