<?php
class Minim_Log implements Minim_Plugin
{
    var $_start_time;
    var $_logfile;

    function enable() // {{{
    {
        $this->_logfile = tempnam('/tmp', 'minim_log_');
        ini_set('error_log', $this->_logfile);
        $this->_start_time = array_sum(explode(' ', microtime()));
        register_shutdown_function(array(&$this, 'shutdown'));
    } // }}}

    function dump($html=TRUE) // {{{
    {
        $msgs = file($this->_logfile);
        $msgs = preg_replace('/^\[[^\]]+\] /', '', $msgs);
        $dump = join('', $msgs);
        if ($html)
        {
            $dump = '<pre>'.htmlspecialchars($dump).'</pre>';
        }
        print $dump;
    } // }}}

    function shutdown() // {{{
    {
        $time = array_sum(explode(' ', microtime())) - $this->_start_time;
        error_log(sprintf("Took %.4fs", $time));
        if (isset($_REQUEST['debug']))
        {
            $this->dump();
        }
    } // }}}
}
