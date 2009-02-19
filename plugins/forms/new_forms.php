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
            $this->_fields[$field_name] = new Minim_Form_Field(
                $field_name, 'text', $params
            );
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

    function render() // {{{
    {
        $fields = '';
        foreach ($this->_fields as $field)
        {
            $fields .= $field->render_as_div();
        }
        return <<<HTML
<form method="{$this->method}" action="{$this->submit_url}"{$this->attrs}>
    {$fields}
</form>
HTML;
    } // }}}
} // }}}

class Minim_Form_Field // {{{
{
    var $name;
    var $type;
    var $label;
    var $help;
    var $value;
    var $initial_value;

    function __construct($name, $type, $params=array()) // {{{
    {
        $this->name = $name;
        $this->type = $type;
        foreach (array('label', 'help', 'value', 'initial_value') as $var)
        {
            $this->$var = @$params[$var] ? $params[$var] : '';
        }
        
    } // }}}

    function render_label() // {{{
    {
        return <<<HTML
<label for="{$this->name}_field">{$this->label}</label>
HTML;
    } // }}}

    function render_field() // {{{
    {
        $value_attr = '';
        if ($this->value)
        {
            $value_attr = ' value="'.$this->value.'"';
        }
        return <<<HTML
<input type="text" name="{$this->name}" id="{$this->name}_field"{$value_attr}>
HTML;
    } // }}}

    function render_help() // {{{
    {
        return <<<HTML
<p class="formfield_help">{$this->help}</p>
HTML;
    } // }}}

    function render_as_div() // {{{
    {
        $label = $this->render_label();
        $field = $this->render_field();
        $help = $this->render_help();
        return <<<HTML
<div class="formfield">
    {$label}
    {$field}
    {$help}
</div>
HTML;
    } // }}}
} // }}}
