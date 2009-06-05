<?php
class Minim_TemplateEngine implements Minim_Plugin 
{
    var $template_paths;
    var $helper_paths;
    var $_helpers;
    var $_def_stack;
    var $_blocks;
    var $_extends;
    var $plugin_path;
    var $asset_paths;

    function Minim_TemplateEngine()
    {
        $this->template_paths = array();
        $this->helper_paths = array(
            path(dirname(__FILE__), 'helper.php')
        );
        $this->_def_stack = array();
        $this->_blocks = array();
        $this->_extends = array();
        $this->_helpers = array();
        $this->plugin_path = dirname(__FILE__);
        $this->asset_paths = array();
    }

    /**
     * Render a template
     */
    function render($templates, $context=array())
    {
        // make sure templates is an array
        if (!is_array($templates))
        {
            $templates = array($templates);
        }

        // use the first template that can be found
        $found = FALSE;
        foreach ($templates as $template)
        {
            if ($found = $this->_find_template($template))
            {
                error_log("Found {$template} template at {$found}");
                break;
            }
        }

        if (!$found)
        {
            throw new Minim_TemplateEngine_Exception(
                "No matching templates found");
        }

        $this->_render($template, $found, $context);
    }

    function _render($_template, $_template_file, $_context)
    {
        // prefix all variables with _ to 'hide' them from the template
        error_log("Rendering $_template template");
        if (!$_template_file)
        {
            throw new Minim_TemplateEngine_Exception(
                "Template $_template not found on template path");
        }
        
        extract($_context);
        ob_start();
        $included = include($_template_file);
        if (!$included)
        {
            throw new Minim_TemplateEngine_Exception(
                "Failed loading $_template template");
        }

        // render extended templates
        if ($_parent = array_pop($this->_extends))
        {
            $this->render($_parent, $_context);
        }
        ob_end_flush();
    }

    /**
     * Search template paths for named template.
     * TODO - caching
     * TODO - template inheritance
     */
    function _find_template($name)
    {
        foreach ($this->template_paths as $path)
        {
            error_log("Searching for $name.php in $path");

            $dir = new DirectoryIterator($path);
            foreach ($dir as $file)
            {
                if (strtolower($file->getFilename()) == strtolower("$name.php"))
                {
                    return $file->getPathname();
                }
            }
        }
        return FALSE;
    }

    /**
     * Set a template block
     */
    function set($name)
    {
        array_push($this->_def_stack, $name);
        ob_start();
    }

    /**
     * End a template block
     */
    function end()
    {
        $name = array_pop($this->_def_stack);
        $this->_blocks[$name] = ob_get_clean();
    }

    /**
     * Retrieve a named block
     */
    function get($name)
    {
        echo @$this->_blocks[$name];
    }

    /**
     * Extend a named template
     */
    function extend($name)
    {
        array_push($this->_extends, $name);
    }

    /**
     * Convenience method to include a css file
     */
    function include_css($name)
    {
        $cssfile = find("$name.css", $this->asset_paths);
        echo <<<HTML
<link rel="stylesheet" type="text/css" href="$cssfile">

HTML;
    }

    /**
     * Load a helper function
     */
    function load_helper($name)
    {
        if (array_key_exists($name, $this->_helpers))
        {
            return;
        }
        $pat = '/function '.$name.'/m';
        foreach ($this->helper_paths as $path)
        {
            $content = file_get_contents($path);
            if (preg_match($pat, $content))
            {
                require_once $path;
                $this->_helpers[$name] = $name;
                return;
            }
        }
        throw new Minim_TemplateEngine_Exception(
            "Helper $name not found");
    }

    function load_helpers()
    {
        foreach (func_get_args() as $arg)
        {
            $this->load_helper($arg);
        }
    }

    /**
     * Call loaded helper functions
     */
    function __call($name, $args)
    {
        if (array_key_exists($name, $this->_helpers))
        {
            return call_user_func_array($this->_helpers[$name], $args);
        }
    }
}

class Minim_TemplateEngine_Exception extends Exception {}
