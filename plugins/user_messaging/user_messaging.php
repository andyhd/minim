<?php
class Minim_UserMessages implements Minim_Plugin
{
    const INFO = 0;
    const WARN = 1;
    const ERROR = 2;

    function Minim_UserMessages() // {{{
    {
        if (!session_id())
        {
            session_start();
        }
        // cache user messages so we don't erase new ones in the render phase
        $this->get_messages();
    } // }}}

    function _msg($msg, $type=Minim_UserMessages::INFO) // {{{
    {
        if (defined('STDOUT'))
        {
            // don't bother if running from CLI
            echo $msg, "\n";
            return;
        }
        if (!is_array(@$_SESSION['user_messages']))
        {
            $_SESSION['user_messages'] = array();
        }
        $_SESSION['user_messages'][] = array($msg, $type);
    } // }}}

    function info($msg) // {{{
    {
        $this->_msg($msg, Minim_UserMessages::INFO);
    } // }}}

    function warn($msg) // {{{
    {
        $this->_msg($msg, Minim_UserMessages::WARN);
    } // }}}

    function error($msg) // {{{
    {
        $this->_msg($msg, Minim_UserMessage::ERROR);
    } // }}}

    function get_messages() // {{{
    {
        static $messages;
        if (!$messages)
        {
            if (@array_key_exists('user_messages', $_SESSION))
            {
                $messages = $_SESSION['user_messages'];
                unset($_SESSION['user_messages']);
            }
            else
            {
                $messages = array();
            }
        }
        return $messages;
    } // }}}
}
