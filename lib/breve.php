<?php
function breve()
{
    static $instance;
    if (!$instance)
    {
        $instance = new Breve();
    }
    return $instance;
}

class Breve
{
    var $managers;

    function Breve()
    {
        $this->managers = array();
    }

    function &manager($model)
    {
        if (!$model)
        {
            return NULL;
        }
        if (!array_key_exists($model, $this->managers))
        {
            $class = "{$model}Manager";
            if (class_exists($class))
            {
                $this->managers[$model] =& new $class();
            }
            else
            {
                $this->managers[$model] =& new BreveManager($model);
            }
            $this->managers[$model]->setModel($model);
            minim()->log("Created manager for $model: ".print_r($this->managers[$model], TRUE));
        }
        return $this->managers[$model];
    }
}

class BreveModel
{
    var $_fields;
    var $_errors;
    var $_validated;

    function BreveModel()
    {
        $this->_fields = array();
        $this->_errors = array();
        $this->_validated = FALSE;
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
            if ($field->getAttribute('read_only'))
            {
                minim()->log("$name field is read-only");
                return FALSE;
            }
            $ret = $field->setValue($value);
            if (!$ret)
            {
                minim()->log("Couldn't set $name to $value");
            }
            else
            {
                $this->_validated = FALSE;
            }
            return $ret;
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
        {
            $falsevar = FALSE;
            return $falsevar;
        }
        return $this->_fields[$name];
    }

    function getValue($name)
    {
        if ($field = $this->getField($name))
        {
            return $field->getValue();
        }
        
        minim()->log(get_class($this).": Can't get field $name - does not exist");
        return NULL;
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

    function isValid()
    {
        if (!$this->_validated)
        {
            $this->_validated = TRUE;
            $errors = array();
            foreach ($this->_fields as $name => $field)
            {
                $errors[] = $field->isValid() ? NULL : "Field $name invalid";
            }
            $errors = array_filter($errors);
            $this->_errors = $errors;
        }
        return empty($this->_errors);
    }

    function errors()
    {
        return $this->_errors;
    }

    function save()
    {
        breve()->manager(get_class($this))->save($this);
    }
}

class BreveField
{
    var $_value;
    var $_attrs;

    function BreveField($attrs=array())
    {
        $this->_value = NULL;
        $this->_attrs = $attrs;
    }

    function setValue($value)
    {
        if (!$this->getAttribute('read_only'))
        {
            return $this->_value = $value;
        }
        return FALSE;
    }

    function getValue()
    {
        return $this->_value;
    }

    function getValueForDb()
    {
        return $this->getValue();
    }

    function setAttribute($name, $value)
    {
        $this->_attrs[$name] = $value;
    }

    function getAttribute($name)
    {
        if (array_key_exists($name, $this->_attrs))
        {
            return $this->_attrs[$name];
        }
        return NULL;
    }

    function isValid()
    {
        if ($this->_value === NULL and ($this->getAttribute('not_null') or
                                        $this->getAttribute('required')))
        {
            return FALSE;
        }
        return TRUE;
    }
}

class BreveInt extends BreveField
{
    function setValue($value)
    {
        if (!is_numeric($value))
        {
            return FALSE;
        }

        return parent::setValue((int) $value);
    }

    function isValid()
    {
        if (is_int($this->_value))
        {
            return TRUE;
        }

        return parent::isValid();
    }
}

class BreveText extends BreveField
{
    function setValue($value)
    {
        if (!is_string($value))
        {
            return FALSE;
        }
        
        # TODO - add unicode support here (mb_strlen)
        $maxlen = $this->getAttribute('maxlength');
        if ($maxlen and strlen($value) > $maxlen)
        {
            # TODO - add unicode support here (mb_substr)
            $value = substr($value, 0, $maxlen);

            # Raise a WARNING?
        }

        return parent::setValue($value);
    }

    function isValid()
    {
        // TODO - add unicode support (mb_strlen)
        $maxlen = $this->getAttribute('maxlength');
        $len = strlen($this->_value);
        if ($maxlen and $len > $maxlen)
        {
            return FALSE;
        }
        if ($this->getAttribute('required') and $len < 1)
        {
            return FALSE;
        }

        return parent::isValid();
    }
}

class BreveSlug extends BreveText
{
    function _slugify($value)
    {
        // TODO - unicode support
        $value = strtolower($value);
        $value = preg_replace('/[^-A-Za-z0-9\s]/', '', $value);
        $value = preg_replace('/\s+/', '-', $value);
        return $value;
    }

    function setValue(&$value)
    {
        $from = $this->getAttribute('from');
        if ($from)
        {
            return FALSE;
        }
        if (is_string($value))
        {
            return parent::setValue($this->_slugify($value));
        }
        return FALSE;
    }

    function getValue()
    {
        $value = parent::getValue();
        $from = $this->getAttribute('from');
        if (!$value and $from)
        {
            return $this->_slugify($from->getValue());
        }
        return $value;
    }

    function isValid()
    {
        $from = $this->getAttribute('from');
        if ($from)
        {
            return $from->isValid();
        }
        return parent::isValid();
    }
}

class BreveTimestamp extends BreveField
{
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

        return parent::setValue($value);
    }

    function getValueForDb()
    {
        if ($this->getAttribute('auto_now'))
        {
            return date('Y-m-d H:i:s');
        }
        if ($this->_value)
        {
            return date('Y-m-d H:i:s', $this->_value);
        }
        return NULL;
    }
}

class BreveManager
{
    var $table = NULL;
    var $model = NULL;

    function setModel($model)
    {
        $this->model = $model;
    }

    function get($id)
    {
        if (!$this->table or !$this->model or !is_numeric($id))
        {
            return NULL;
        }
        $sql = <<<SQL
            SELECT *
            FROM {$this->table}
            WHERE id=:id
SQL;
        $s = minim()->db()->prepare($sql);
        $s->execute(array(':id' => $id));
        return new $this->model($s->fetch());
    }

    function save($instance)
    {
        if (!$instance->isValid())
        {
            minim()->log("Cannot save invalid model");
            return FALSE;
        }
        $updates = array();
        $data = array();
        $id = $instance->getValue('id');
        if ($id)
        {
            // assume this is an UPDATE
            $sql = "UPDATE {$this->table} SET %s WHERE id=:id";
            $data[':id'] = $id;
        }
        else
        {
            // this must be an INSERT
            $sql = "INSERT INTO {$this->table} SET %s";

        }
        foreach ($instance->_fields as $name => $field)
        {
            if ($name != 'id')
            {
                $updates[] = "$name = :$name";
                $data[":$name"] = $field->getValueForDb();
            }
        }
        $sql = sprintf($sql, join(', ', $updates));
        $s = minim()->db()->prepare($sql);
        return $s->execute($data);
    }
}
