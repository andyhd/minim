<?php
class Minim_New_Forms implements Minim_Plugin // {{{
{
    function create() // {{{
    {
        return new Minim_Form();
    } // }}}

    function from_model($manager, $model=NULL) // {{{
    {
        $form = new Minim_Form();
        try
        {
            $manager = minim('orm')->$manager;
        }
        catch (Minim_Orm_Exception $e)
        {
            throw new Minim_Forms_Exception(
                "Cannot create form from non-existant model $manager");
        }
        foreach ($manager->_fields as $name => $field)
        {
            # try to get a widget for the form field:
            # 1. see if there's a suggested widget in the model field
            # 2. if not, go with a default text field
            # widgets can be overridden later
            $widget = 'text';
            if ($field->widget)
            {
                $widget = $field->widget;
            }

            # add a form field
            $form->$widget($name);
        }
        error_log(print_r($form, TRUE));
        return $form;
    } // }}}
} // }}}

class Minim_Form // {{{
{
    var $_fields;
    var $submit_url;
    var $method;
    var $attrs;

    function Minim_Form($action='', $method='POST', $attrs=array()) // {{{
    {
        $this->submit_url = $action;
        $this->method = $method;
        $this->attrs = $attrs;
        $this->_fields = array();
    } // }}}

    /**
     * Check for form data matching this form's fields in the request
     */
    function was_submitted() // {{{
    {
        $data = $GLOBALS["_{$this->method}"];
        if (!$data)
        {
            return FALSE;
        }
        $fields_missing = FALSE;
        $submission = array();
        foreach ($this->_fields as $field)
        {
            if (!array_key_exists($field->name, $data))
            {
                return FALSE;
            }
            $submission[$field->name] = $data[$field->name];
        }
        $this->populate($submission);
        return TRUE;
    } // }}}

    /**
     * Validate form submission
     */
    function is_valid() // {{{
    {
        foreach ($this->_fields as &$field)
        {
            if (!$field->is_valid())
            {
                return FALSE;
            }
        }
        return TRUE;
    } // }}}

    /**
     * Set a form's values to those in the specified array
     */
    function populate($data) // {{{
    {
        foreach ($this->_fields as $field)
        {
            $field->value = $data[$field->name];
        }
    } // }}}

    function __call($name, $params) // {{{
    {
        if ($name == 'text')
        {
            $field_name = array_shift($params);
            $params = array_shift($params);
            $this->_fields[$field_name] = new Minim_Form_Field(
                $field_name, 'text', $this, $params
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
    var $form;

    function __construct($name, $type, $form, $params=array()) // {{{
    {
        $this->name = $name;
        $this->type = $type;
        $this->form = $form;
        foreach (array('label', 'help', 'value', 'initial_value') as $var)
        {
            $this->$var = @$params[$var] ? $params[$var] : '';
        }
        $this->_validation_method = @$params['validate'];
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

    function is_valid() // {{{
    {
        $validate = $this->_validation_method;
        if ($validate)
        {
            return $validate($this);
        }
        return TRUE;
    } // }}}
} // }}}
