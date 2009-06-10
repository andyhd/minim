<?php
class Minim_Config implements Minim_Plugin
{
    var $_config = array();

    function __get($name)
    {
        return $this->_config[$name];
    }

    function get($name, $default)
    {
        if (array_key_exists($name, $this->_config))
        {
            return $this->_config[$name];
        }
        return $default;
    }

    function load($config_file)
    {
        if (!file_exists($config_file))
        {
            throw new Minim_Config_Exception(
                "Configuration file $config_file not found");
        }
        include $config_file;
        $this->_config = get_defined_vars();
    }
}
