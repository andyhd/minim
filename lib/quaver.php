<?php
class MinimForm
{
    var $_model;
    var $_params;
    var $_fields;

    function __construct($model, $params=array())
    {
        $this->_model = $model;
        $this->_params = $params;
        $this->_fields = array();

        // build default form from model
        $manager = breve()->manager($model);
        foreach ($manager->_fields as $name => $field)
        {
            switch (get_class($field))
            {
                case 'BreveTimestamp':
                    $this->dateField($name);
                    break;
                default:
                    $this->textField($name);
            }
        }
    }

    function hiddenField($name, $params=array())
    {
        $this->_fields[$name] = new MinimHidden($name, $params);
        return $this;
    }

    function textField($name, $params=array())
    {
        $this->_fields[$name] = new MinimText($name, $params);
        return $this;
    }

    function dateField($name, $params=array())
    {
        $this->_fields[$name] = new MinimDate($name, $params);
        return $this;
    }

    function textArea($name, $params=array())
    {
        $this->_fields[$name] = new MinimTextArea($name, $params);
        return $this;
    }

    function __get($name)
    {
        if (array_key_exists($name, $this->_fields))
        {
            $field =& $this->_fields[$name];
            return $field;
        }

        minim()->log("MinimForm {$this->_name} has no field named {$name}");
        return NULL;
    }
}

class MinimInput
{
    var $_initial;
    var $_name;
    var $_attrs;
    var $_id;
    var $_class;

    var $label;
    
    function __construct($name, $params)
    {
        $this->_name = $name;
        $this->_attrs = $params;
        if (array_key_exists('initial', $this->_attrs))
        {
            $this->_initial = $params['initial'];
            unset($this->_attrs['initial']);
        }
        if (!array_key_exists('id', $this->_attrs))
        {
            $this->_id = "{$this->_name}_id";
        }
        else
        {
            $this->_id = $this->_attrs['id'];
            unset($this->_attrs['id']);
        }
        if (array_key_exists('classes', $this->_attrs))
        {
            $this->_class = ' class="'.join(' ', $this->_attrs['classes']).'"';
            unset($this->_attrs['classes']);
        }
        $label = ucfirst($this->_name);
        if (array_key_exists('label', $this->_attrs))
        {
            $label = $this->_attrs['label'];
            unset($this->_attrs['label']);
        }
        $this->label = <<<PHP
<label for="{$this->_id}">{$label}</label>
PHP;
    }

    function getValue()
    {
        return $this->_initial;
    }

    function render()
    {
        die('MinimInput::render must be overridden');
    }
}

class MinimHidden extends MinimInput
{
    function render()
    {
        return <<<PHP
<input type="hidden" name="{$this->_name}" value="{$this->getValue()}">
PHP;
    }
}

class MinimText extends MinimInput
{
    function render()
    {
        return <<<PHP
<input id="{$this->_id}" type="text" name="{$this->_name}" value="{$this->getValue()}"{$this->_class}>
PHP;
    }
}

class MinimDate extends MinimInput
{
    function render()
    {
        return <<<PHP
<input id="{$this->_id}" type="text" name="{$this->_name}" value="{$this->getValue()}"{$this->_class}>
PHP;
    }
}

class MinimTextArea extends MinimInput
{
    function render()
    {
        $rows = (int) @$this->_attrs['rows'];
        $rows = $rows ? ' rows="'.$rows.'"' : '';
        return <<<PHP
<textarea id="{$this->_id}" name="{$this->_name}"{$rows}{$this->_class}>{$this->getValue()}</textarea>
PHP;
    }
}
