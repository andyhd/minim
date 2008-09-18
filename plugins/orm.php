<?php
/**
 * ORM plugin implements Data Objects, Managers, Model Sets, lazy loading
 *
 * Registering a new model:
 * ------------------------
 *
 *   minim('orm')->register_model($model_name)
 *               ->table($table_name)
 *               ->default_sort($order)
 *               ->{$field_type}($name, $params_array)
 *               ...;
 *
 * Querying database - get a list of models:
 * -----------------------------------------
 *
 *   $models = minim('orm')->{$model}
 *                         ->all();
 *
 *   $models = minim('orm')->{$model}
 *                         ->filter($criteria_array)
 *                         ...
 *                         ->order_by($order)
 *                         ->limit($limit);
 *
 * Accessing model values:
 * -----------------------
 *
 *   $value = minim('orm')->{$model}
 *                        ->get($id)
 *                        ->{$field_name};
 *
 * Saving / Deleting models:
 * -------------------------
 *
 *   minim('orm')->{$model}
 *               ->from($data_array)
 *               ->save();
 *
 *   minim('orm')->{$model}
 *               ->get($id)
 *               ->delete();
 *
 **/

class Minim_DataObject // {{{
{
    var $_type;
    var $_name;
    var $_fields;
    var $_errors;
    var $_validated;
    var $_json;
    var $_default_sort;

    function Minim_DataObject($name) // {{{
    {
        $this->_type = NULL;
        $this->_name = $name;
        $this->_fields = array();
        $this->_errors = array();
        $this->_validated = FALSE;
        $this->_json = NULL;
    } // }}}

    function _setFields(&$fields) // {{{
    {
        // no references results in copies?
        foreach ($fields as $name => $field)
        {
            $clone =& $field->copy();
            if ($field->type == "slug")
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
    } // }}}

    function setValue($name, $value) // {{{
    {
        if ($field = $this->_getField($name))
        {
            if ($field->getAttribute('read_only'))
            {
                minim('log')->debug("$name field is read-only");
                return FALSE;
            }
            $ret = $field->setValue($value);
            if ($ret === FALSE)
            {
                minim('log')->debug("Couldn't set $name to ".print_r($value, TRUE));
            }
            else
            {
                $this->_validated = FALSE;
            }
            return $ret;
        }

        die(get_class($this).": Can't set field $name - does not exist");
    } // }}}

    function __set($name, $value) // {{{
    {
        return $this->setValue($name, $value);
    } // }}}

    function &_getField($name) // {{{
    {
        if (!array_key_exists($name, $this->_fields))
        {
            $falsevar = FALSE;
            return $falsevar;
        }
        return $this->_fields[$name];
    } // }}}

    function getValue($name) // {{{
    {
        if ($field = $this->_getField($name))
        {
            return $field->getValue();
        }
        
        minim('log')->debug(get_class($this).": Can't get field $name - does not exist");
        return NULL;
    } // }}}

    function __get($name) // {{{
    {
        return $this->getValue($name);
    } // }}}

    function _fromArray($data) // {{{
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
    } // }}}

    function isValid() // {{{
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
    } // }}}

    function errors() // {{{
    {
        return $this->_errors;
    } // }}}

    function save() // {{{
    {
        breve($this->_name)->save($this);
    } // }}}

    function delete() // {{{
    {
        breve($this->_name)->delete($this);
    } // }}}

    function to_array() // {{{
    {
        $ar = array();
        foreach ($this->_fields as $name => $field)
        {
            $ar[$name] = $field->getValue();
        }
        return $ar;
    } // }}}
} // }}}

class Minim_Orm_Field // {{{
{
    var $_type;
    var $_value;
    var $_attrs;

    function Minim_Orm_Field($type, $attrs=array()) // {{{
    {
        $this->_type = $type;
        $this->_value = NULL;
        $this->_attrs = $attrs;
    } // }}}

    function &copy() // {{{
    {
        $class = get_class($this);
        $clone =& new $class(array_filter($this->_attrs));
        return $clone;
    } // }}}

    function setValue($value) // {{{
    {
        if (!$this->getAttribute('read_only'))
        {
            return $this->_value = $value;
        }
        return FALSE;
    } // }}}

    function getValue() // {{{
    {
        return $this->_value;
    } // }}}

    function getValueForDb() // {{{
    {
        return $this->getValue();
    } // }}}

    function setAttribute($name, $value) // {{{
    {
        $this->_attrs[$name] = $value;
    } // }}}

    function getAttribute($name) // {{{
    {
        if (array_key_exists($name, $this->_attrs))
        {
            return $this->_attrs[$name];
        }
        return NULL;
    } // }}}

    function isValid() // {{{
    {
        if ($this->_value === NULL and ($this->getAttribute('not_null') or
                                        $this->getAttribute('required')))
        {
            return FALSE;
        }
        return TRUE;
    } // }}}
} // }}}

class Minim_Orm_Int extends Minim_Orm_Field // {{{
{
    function setValue($value) // {{{
    {
        if (!is_numeric($value))
        {
            return FALSE;
        }

        return parent::setValue((int) $value);
    } // }}}

    function isValid() // {{{
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
    } // }}}
} // }}}

class Minim_Orm_Text extends Minim_Orm_Field // {{{
{
    function setValue($value) // {{{
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
    } // }}}

    function isValid() // {{{
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
    } // }}}
} // }}}

class Minim_Orm_Slug extends Minim_Orm_Text // {{{
{
    function _slugify($value) // {{{
    {
        // TODO - unicode support
        $value = strtolower($value);
        $value = preg_replace('/[^-A-Za-z0-9\s]/', '', $value);
        $value = preg_replace('/\s+/', '-', $value);
        return $value;
    } // }}}

    function setValue(&$value) // {{{
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
    } // }}}

    function getValue() // {{{
    {
        $value = parent::getValue();
        $from = $this->getAttribute('from');
        if (!$value and $from)
        {
            return $this->_slugify($from->getValue());
        }
        return $value;
    } // }}}

    function isValid() // {{{
    {
        $from = $this->getAttribute('from');
        if ($from)
        {
            return $from->isValid();
        }
        return parent::isValid();
    } // }}}
} // }}}

class Minim_Orm_Timestamp extends Minim_Orm_Field // {{{
{
    function setValue($value) // {{{
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
    } // }}}

    function getValueForDb() // {{{
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
    } // }}}
} // }}}

class Minim_Orm_Manager // {{{
{
    var $_model;
    var $_table;
    var $_fields;
    var $_default_sort;

    function Minim_Orm_Manager($model) // {{{
    {
        $this->_model = $model;
        $this->_fields = array();
        $this->_default_sort = NULL;
    } // }}}

    function &table($name=NULL) // {{{
    {
        if (is_null($name))
        {
            // don't return a reference to $this->_table - might get corrupted
            $copy = $this->_table;
            return $copy;
        }
        $this->_table = $name;
        return $this;
    } // }}}

    function &__call($name, $args) // {{{
    {
        $field_types = minim('orm')->_available_field_types();
        $key = strtolower($name);
        if (array_key_exists($key, $field_types))
        {
            $field =& $field_types[$key];
            require_once $field['file'];
            $field_name = array_shift($args);
            $params = array_shift($args);
            $this->_fields[$field_name] =& new $field['class']($name, $params);
            return $this;
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function &get($id) // {{{
    {
        $ms = new Minim_Orm_ModelSet($this->_model);
        $ms->filter(array(
            'id__eq' => $id,
        ));
        return $ms;
    } // }}}

    function default_sort($sort = NULL) // {{{
    {
        if ($sort)
        {
            $this->_default_sort = $sort;
            return $this;
        }
        return $this->_default_sort;
    } // }}}

    function &all() // {{{
    {
        $ms = new Minim_Orm_ModelSet($this->_model);
        return $ms;
    } // }}}

    function &filter($kwargs=array()) // {{{
    {
        $ms = $this->all()->filter($kwargs);
        return $ms;
    } // }}}

    function &from($data) // {{{
    {
        $model =& new Minim_DataObject($this->_model);
        $model->_setFields($this->_fields);

        if (!is_array($data))
        {
            return $model;
        }

        $model->_fromArray($data);

        return $model;
    } // }}}

    function delete(&$instance) // {{{
    {
        $id = $instance->getValue('id');
        $sql = "DELETE FROM {$this->_table} WHERE id=:id";
        $data = array(':id' => $id);
        $s = minim()->db()->prepare($sql);
        return $s->execute($data);
    } // }}}

    function save(&$instance) // {{{
    {
        if (!$instance->isValid())
        {
            minim('log')->debug("Cannot save invalid model");
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
            minim('log')->debug("Setting id of new {$this->_model} to {$ret['last_insert_id']}"); 
            $instance->id = $ret['last_insert_id'];
        }
        return $ret;
    } // }}}
} // }}}

class Minim_Orm implements Minim_Plugin // {{{
{
    var $_models;
    var $_managers;
    var $_field_types;

    function Minim_Orm() // {{{
    {
        $this->_models = array();
        $this->_managers = array();
        $this->_field_types = array();
    } // }}}

    // model construction methods
    function &_available_field_types() // {{{
    {
        if (!$this->_field_types)
        {
            // get a list of available field_types
            $pluginsdir = minim()->root.'/plugins';
            $dh = opendir($pluginsdir);
            if (!$dh)
            {
                die("_available_field_types() - Plugins directory not found");
            }
            $pat = '/class\s+([^\s]+)\s+extends\s+Minim_Orm_Field/m';
            while ($dl = readdir($dh))
            {
                if (substr($dl, -4) == '.php')
                {
                    $file = "$pluginsdir/$dl";
                    $contents = file_get_contents($file);

                    // check for Minim_Orm_Field subclasses
                    if (preg_match_all($pat, $contents, $m))
                    {
                        foreach ($m[1] as $class)
                        {
                            $type = strtolower(substr($class, 10));
                            $this->_field_types[$type] = array(
                                'file' => $file,
                                'class' => $class
                            );
                        }
                    }
                }
            }
            minim('log')->debug("ORM field types available: ".
                print_r(array_keys($this->_field_types), TRUE));
        }
        return $this->_field_types;
    } // }}}

    function &_build_field($type, $args=array()) // {{{
    {
        if (!$this->_field_types)
        {
            $this->_discover_field_types();
        }
        $key = strtolower($type);
        if (array_key_exists($key, $this->_field_types))
        {
            $field =& $this->_field_types[$key];
            require_once $field['file'];
            $instance =& new $field['class'];
            return $instance;
        }
        die("ORM field type $type not found");
    } // }}}

    function &__call($name, $args) // {{{
    {
        if (array_key_exists($name, $this->_field_types))
        {
            $field =& $this->_build_field($name, $args);
            return $field;
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function &register_model($model) // {{{
    {
        if (!array_key_exists($model, $this->_managers))
        {
            $this->_managers[$model] =& new Minim_Orm_Manager($model);
        }
        return $this->_managers[$model];
    } // }}}

    // query methods
    function &_manager($model) // {{{
    {
        if (array_key_exists($model, $this->_managers))
        {
            return $this->_managers[$model];
        }
        else
        {
            if (!$this->_models)
            {
                // find models
                $this->models_available();
            }
            if (array_key_exists($model, $this->_models))
            {
                include minim()->root."/models/{$this->_models[$model]}";
                if (array_key_exists($model, $this->_managers))
                {
                    return $this->_managers[$model];
                }
            }
        }

        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function &_available_models() // {{{
    {
        if (!$this->_models)
        {
            $pat = '/minim\(([\'"])orm\1\)->register_model\(\s*([\'"])'.
                   '([a-zA-Z]+)'.
                   '\2\s*\)/x';
            
            // check each model file
            $model_dir = minim()->root."/models";
            $dh = opendir($model_dir);
            while ($file = readdir($dh))
            {
                if (substr($file, -4) == '.php')
                {
                    $contents = file_get_contents("$model_dir/$file");

                    // look for a model registration call
                    if (preg_match_all($pat, $contents, $match))
                    {
                        foreach ($match[3] as $model)
                        {
                            $this->_models[$model] = $file;
                        }
                    }
                }
            }
            minim('log')->debug("Models available: ".
                                print_r(array_keys($this->_models), TRUE));
        }
        return $this->_models;
    } // }}}

    function &__get($name) // {{{
    {
        if (array_key_exists($name, $this->_available_models()))
        {
            if (array_key_exists($name, $this->_managers))
            {
                return $this->_managers[$name];
            }
            else
            {
                include minim()->root."/models/{$this->_models[$name]}";
                if (array_key_exists($name, $this->_managers))
                {
                    return $this->_managers[$name];
                }
            }
        }
    } // }}}

    function foreignKey($params=array()) // {{{
    {
        return array('BreveForeignKey' => $params);
    } // }}}
} // }}}
