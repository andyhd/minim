<?php
class Minim_New_Forms implements Minim_Plugin // {{{
{
    var $_widget_types;
    var $widget_paths;

    function Minim_New_Forms() // {{{
    {
        $file = realpath(__FILE__);
        $this->_widget_types = array(
            'text' => array('class' => 'Minim_Form_Field',
                            'file' => $file),
            'textarea' => array('class' => 'Minim_Form_TextArea',
                                'file' => $file),
            'select' => array('class' => 'Minim_Form_Select',
                              'file' => $file),
            'radio' => array('class' => 'Minim_Form_RadioGroup',
                             'file' => $file),
            'checkbox' => array('class' => 'Minim_Form_CheckBox',
                                'file' => $file)
            // TODO - add more default widgets
        );
        $this->widget_paths = array();
    } // }}}

    /**
     * Register a widget type 
     */
    function register_widget_type($type, $file, $class_name) // {{{
    {
        $this->_widget_types[$type] = array(
            'file' => $file,
            'class' => $class_name
        );
    } // }}}

    function create() // {{{
    {
        return new Minim_Form($this);
    } // }}}

    function from_model($manager, $model=NULL) // {{{
    {
        $form = new Minim_Form($this);
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
            # TODO - add field params as argument
            $form->$widget($name);
        }
        error_log(print_r($form, TRUE));
        return $form;
    } // }}}
} // }}}

class Minim_Form // {{{
{
    var $_forms;
    var $_fields;
    var $submit_url;
    var $method;
    var $attrs;

    function Minim_Form(&$fo, $action='', $method='POST', $attrs=array()) // {{{
    {
        $this->_forms =& $fo;
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

    /**
     * Enable syntactic sugar for adding fields to a form.
     */
    function &__call($name, $params) // {{{
    {
        if (array_key_exists($name, $this->_forms->_widget_types))
        {
            $field_name = array_shift($params);
            $params = array_shift($params);
            if (!$params)
            {
                $params = array();
            }
            $this->add_field($name, $field_name, $params);
        }
        return $this;
    } // }}}

    /**
     * Add a field to a form
     */
    function add_field($widget, $name, $params) // {{{
    {
        $widgets = $this->_forms->_widget_types;
        if (array_key_exists($widget, $widgets))
        {
            // check class loaded
            if (!class_exists($widgets[$widget]['class']))
            {
                require_once $widgets[$widget]['file'];
            }

            // instantiate widget object
            $params = array_merge($params, array(
                'form' => &$this,
                'name' => $name
            ));
            $this->_fields[$name] = new $widgets[$widget]['class']($params);
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

    function __construct($params=array()) // {{{
    {
        $this->name = $params['name'];
        $this->type = 'text';
        $this->form =& $params['form'];
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
