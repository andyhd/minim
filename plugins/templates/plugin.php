<?php
class Minim_TemplateEngine 
{
    var $_template_paths;
    var $_def_stack;
    var $_blocks;
    var $_extends;

    function Minim_TemplateEngine() // {{{
    {
        $this->_template_paths = array();
        $this->_def_stack = array();
        $this->_blocks = array();
        $this->_extends = array();
    } // }}}

    function add_template_path($path) // {{{
    {
        $this->_template_paths[] = $path;
    } // }}}

    /**
     * Render a template
     */
    function render($_template, $_context=array()) // {{{
    {
        $_template_file = $this->_find_template($_template);
        if (!$_template_file)
        {
            throw new Minim_TemplateEngine_Exception(
                "Template $_template not found on template path");
        }
        extract($_context);
        ob_start();
        include $_template_file;

        // render extended templates
        if ($_parent = array_pop($this->_extends))
        {
            $this->render($_parent, $_context);
        }
        ob_end_flush();
    } // }}}

    /**
     * Search template paths for named template.
     * TODO - caching
     * TODO - template inheritance
     */
    function _find_template($name) // {{{
    {
        foreach ($this->_template_paths as $path)
        {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file)
            {
                if (strtolower($file->getFilename()) == "$name.php")
                {
                    return $file->getPathname();
                }
            }
        }
        return FALSE;
    } // }}}

    /**
     * Set a template block
     */
    function set($name) // {{{
    {
        array_push($this->_def_stack, $name);
        ob_start();
    } // }}}

    /**
     * End a template block
     */
    function end() // {{{
    {
        $name = array_pop($this->_def_stack);
        $this->_blocks[$name] = ob_get_clean();
    } // }}}

    /**
     * Retrieve a named block
     */
    function get($name) // {{{
    {
        echo $this->_blocks[$name];
    } // }}}

    /**
     * Extend a named template
     */
    function extend($name) // {{{
    {
        array_push($this->_extends, $name);
    } // }}}
}

class Minim_TemplateEngine_Exception extends Exception {}
