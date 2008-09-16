<?php
class Minim_Log implements Minim_Plugin
{
    var $_start_time;
    var $_msgs;

    function Minim_Log() // {{{
    {
        $this->_start_time = array_sum(explode(' ', microtime()));
        $this->_msgs = array();
        register_shutdown_function(array(&$this, 'shutdown'));
    } // }}}

    function debug($msg) // {{{
    {
        $this->_msgs[] = $msg;
    } // }}}

    function dump($html=TRUE) // {{{
    {
        $dump = join("\n", $this->_msgs);
        if ($html)
        {
            $dump = "<pre>$dump</pre>";
        }
        print $dump;
    } // }}}

    function shutdown() // {{{
    {
        $time = array_sum(explode(' ', microtime())) - $this->_start_time;
        $this->debug(sprintf("Took %.4fs", $time));
        if (isset($_REQUEST['debug']))
        {
            $this->dump();
        }
    } // }}}
}
