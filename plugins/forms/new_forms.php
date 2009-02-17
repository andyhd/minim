<?php
class Minim_New_Forms implements Minim_Plugin // {{{
{
    function create() // {{{
    {
        return new Minim_Form();
    } // }}}
} // }}}

class Minim_Form // {{{
{
    var $_fields;

    function Minim_Form() // {{{
    {
        $this->_fields = array();
    } // }}}

    function __call($name, $params) // {{{
    {
        if ($name == 'text')
        {
            $field_name = array_shift($params);
            $this->_fields[$field_name] = array_merge($params, array(
                'type' => 'text'
            ));
        }
    } // }}}

    function __get($name) // {{{
    {
        if (array_key_exists($name, $this->_fields))
        {
            return $this->_fields[$name];
        }
        return NULL;
    } // }}}
} // }}}
