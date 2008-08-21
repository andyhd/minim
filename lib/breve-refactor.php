<?php
class BreveModel
{
    var $_name;
    var $_fields;
    var $_errors;
    var $_validated;
    var $_json;

    function BreveModel($name)
    {
        $this->_name = $name;
        $this->_fields = array();
        $this->_errors = array();
        $this->_validated = FALSE;
        $this->_json = NULL;
    }

    function _setFields(&$fields)
    {
        // no references results in copies?
        foreach ($fields as $name => $field)
        {
            $clone =& $field->copy();

            // TODO - find a less hacky way to do this
            if ($field instanceof BreveSlug)
            {
                $from = $clone->getAttribute('from');
                if (is_null($from) or !array_key_exists($from, $this->_fields))
                {
                    die("{$this->_model} has no field {$from} for slug");
                }
                $clone->setAttribute('from', &$this->_fields[$from]);
            }
            $this->_fields[$name] =& $clone;
        }
    }

    function setValue($name, $value)
    {
        if ($field = $this->_getField($name))
        {
            if ($field->getAttribute('read_only'))
            {
                minim()->log("$name field is read-only");
                return FALSE;
            }
            $ret = $field->setValue($value);
            if (!$ret)
            {
                minim()->log("Couldn't set $name to ".print_r($value, TRUE));
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

    function &_getField($name)
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
        if ($field = $this->_getField($name))
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
            if ($this->_getField($key))
            {
                $this->setValue($key, $value);
            }
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
        breve($this->_name)->save($this);
    }

    function delete()
    {
        breve($this->_name)->delete($this);
    }

    function to_array()
    {
        $ar = array();
        foreach ($this->_fields as $name => $field)
        {
            $ar[$name] = $field->getValue();
        }
        return $ar;
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

    function &copy()
    {
        $class = get_class($this);
        $clone =& new $class(array_filter($this->_attrs));
        return $clone;
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
        if ($this->getAttribute('autoincrement') and is_null($this->_value))
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
    var $_model;
    var $_table;
    var $_fields;

    function BreveManager($model)
    {
        $this->_model = $model;
        $this->_fields = array();
    }

    function &table($name=NULL)
    {
        if (is_null($name))
        {
            // don't return a reference to $this->_table - might get corrupted
            $copy = $this->_table;
            return $copy;
        }
        $this->_table = $name;
        return $this;
    }

    function &field($name, &$field=NULL)
    {
        if (is_null($field))
        {
            $copy = @$this->_fields[$name];
            return $copy;
        }
        $this->_fields[$name] = $field;
        return $this;
    }

    function &get($id)
    {
        $ms = new BreveModelSet($this->_model);
        $ms->filter(array(
            'id__eq' => $id,
        ));
        return $ms;
    }

    function &all()
    {
        $ms = new BreveModelSet($this->_model);
        return $ms;
    }

    function &filter($kwargs=array())
    {
        $ms = $this->all()->filter($kwargs);
        return $ms;
    }

    function &from($data)
    {
        $model =& new BreveModel($this->_model);
        $model->_setFields($this->_fields);

        if (!is_array($data))
        {
            return $model;
        }

        $model->_fromArray($data);

        return $model;
    }

    function delete(&$instance)
    {
        $id = $instance->getValue('id');
        $sql = "DELETE FROM {$this->_table} WHERE id=:id";
        $data = array(':id' => $id);
        $s = minim()->db()->prepare($sql);
        return $s->execute($data);
    }

    function save(&$instance)
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
            $sql = "UPDATE {$this->_table} SET %s WHERE id=:id";
            $data[':id'] = $id;
        }
        else
        {
            // this must be an INSERT
            $sql = "INSERT INTO {$this->_table} SET %s";
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
        $ret = $s->execute($data);
        if (!$id)
        {
            minim()->log("Setting id of new {$this->_model} to {$ret['last_insert_id']}"); 
            $instance->id = $ret['last_insert_id'];
        }
        return $ret;
    }
}

class Breve
{
    var $_managers;

    function Breve()
    {
        $this->_managers = array();
    }

    function &register($model)
    {
        if (!array_key_exists($model, $this->_managers))
        {
            $this->_managers[$model] =& new BreveManager($model);
        }
        return $this->_managers[$model];
    }

    function &manager($model)
    {
        if (array_key_exists($model, $this->_managers))
        {
            return $this->_managers[$model];
        }

        $nullVar = NULL;
        return $nullVar;
    }

    function &__get($name)
    {
        if (array_key_exists($name, $this->_managers))
        {
            return $this->_managers[$name];
        }

        // cannot return NULL by reference
        $null_var = NULL;
        return $null_var;
    }

    function int($params=array())
    {
        return new BreveInt($params);
    }

    function text($params=array())
    {
        return new BreveText($params);
    }

    function timestamp($params=array())
    {
        return new BreveTimestamp($params);
    }

    function slug($params=array())
    {
        return new BreveSlug($params);
    }

    function foreignKey($params=array())
    {
        return array('BreveForeignKey' => $params);
    }
}

function &breve($model=NULL)
{
    static $instance;
    if (!$instance)
    {
        $instance = new Breve();
    }

    if ($model)
    {
        return $instance->manager($model);
    }

    return $instance;
}
