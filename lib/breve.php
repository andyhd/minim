<?php
class BreveModel
{
    var $_fields;
    var $_table;

    function BreveModel()
    {
        $this->_fields = array();
        $this->define(); // set up fields
        
        // allow prepopulating
        $argv = func_get_args();
        $argc = func_num_args();
        if ($argc == 1)
        {
            $arg = $argv[0];
            if (is_array($arg))
            {
                $this->_fromArray($arg);
            }
        }
        elseif ($argc > 1)
        {
            // attempt treating args as fields in sequence
            $keys = array_keys($this->_fields);
            $params = array_combine($keys, $argv);
            $this->_fromArray($params);
        }
    }

    function define()
    {
        // should be overridden
    }

    function setField($name, &$field)
    {
        $this->_fields[$name] = $field;
    }

    function setValue($name, $value)
    {
        if ($field = $this->getField($name))
        {
            return $field->setValue($value);
        }

        die(get_class($this).": Can't set field $name - does not exist");
    }

    function __set($name, $value)
    {
        return $this->setValue($name, $value);
    }

    function &getField($name)
    {
        if (!array_key_exists($name, $this->_fields))
            return FALSE;
        return $this->_fields[$name];
    }

    function getValue($name)
    {
        if ($field = $this->getField($name))
        {
            return $field->getValue();
        }
        
        die(get_class($this).": Can't get field $name - does not exist");
    }

    function __get($name)
    {
        return $this->getValue($name);
    }

    function _fromArray($data)
    {
        if (!is_array($data))
        {
            return $this;
        }

        foreach ($data as $key => $value)
        {
            $this->setValue($key, $value);
        }

        return $this;
    }

    function get($params = array())
    {
        if (!$params)
        {
            return NULL;
        }
        $sql = sprintf('SELECT * FROM %s WHERE %s', $this->_table, $criteria);
    }
}

class BreveField
{
    var $_value;

    function __construct($params)
    {
        $this->_value = NULL;
    }

    function setValue($value)
    {
        return $this->_value = $value;
    }

    function getValue()
    {
        return $this->_value;
    }
}

class BreveInt extends BreveField
{
    var $_autoincrement;

    function BreveInt($params = array())
    {
        $this->__construct($params);
    }

    function __construct($params = array())
    {
        parent::__construct($params);
        if (@$params['autoincrement'])
        {
            $this->_autoincrement = $params['autoincrement'];
        }
    }

    function setValue($value)
    {
        if (!is_int($value))
        {
            return FALSE;
        }

        return $this->_value = $value;
    }
}

class BreveChar extends BreveField
{
    var $_maxlength;

    function BreveChar($params = array())
    {
        $this->__construct($params);
    }

    function __construct($params = array())
    {
        parent::__construct($params);
        if (@$params['maxlength'])
        {
            $this->_maxlength = $params['maxlength'];
        }
    }

    function setValue($value)
    {
        if (!is_string($value))
        {
            return FALSE;
        }
        
        # TODO - add unicode support here (mb_strlen)
        if ($this->_maxlength and strlen($value) > $this->_maxlength)
        {
            # TODO - add unicode support here (mb_substr)
            $value = substr($value, 0, $this->_maxlength);

            # Raise a WARNING?
        }

        return $this->_value = $value;
    }
}

class BreveSlug extends BreveChar
{
    var $_from;

    function BreveSlug($params = array())
    {
        $this->__construct($params);
    }

    function __construct($params = array())
    {
        parent::__construct($params);
        if (@$params['from'])
        {
            $this->_from = $params['from'];
        }
    }

    function setValue($value)
    {
        // slugs are read-only
        return FALSE;
    }

    function getValue()
    {
        // get value by slugifying _from field
        if (is_null($this->_from))
        {
            return NULL;
        }

        $value = $this->_from->getValue();
        if ($value === FALSE)
        {
            return FALSE;
        }

        if ($value === '')
        {
            return '';
        }

        # TODO - unicode support
        $value = strtolower($value);
        $value = preg_replace('/\s+/-/', $value);
        return $value;
    }
}

class BreveText extends BreveChar
{
    function BreveText($params = array())
    {
        $this->__construct($params);
    }

    function __construct($params = array())
    {
        parent::__construct($params);
    }

    function setValue($value)
    {
        if (!is_string($value))
        {
            return FALSE;
        }

        return $this->_value = $value;
    }
}

class BreveTimestamp extends BreveField
{
    function BreveTimestamp($params = array())
    {
        $this->__construct($params);
    }

    function __construct($params = array())
    {
        parent::__construct($params);
    }

    function setValue($value)
    {
        // TODO - don't use unix timestamps
        if (is_string($value))
        {
            $value = strtotime($value);
            if (!$value)
            {
                return FALSE;
            }
        }
        elseif (is_int($value))
        {
            if ($value < 0)
            {
                return FALSE;
            }
        }

        return $this->_value = $value;
    }
}
