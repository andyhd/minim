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
 * Create database tables from models
 * ----------------------------------
 *
 *   minim('orm')->create_database_tables();
 *
 **/

class Minim_DataObject // {{{
{
    var $_name;
    var $_fields;
    var $_default_sort;

    function Minim_DataObject($name) // {{{
    {
        $this->_name = $name;
        $this->_fields = array();
    } // }}}

    function _setFields(&$fields) // {{{
    {
        // no references results in copies?
        foreach ($fields as $name => $field)
        {
            $clone =& $field->copy();
            if ($field->_type == "slug")
            {
                $from = $clone->attr('from');
                if (is_null($from) or !array_key_exists($from, $this->_fields))
                {
                    die("{$this->_model} has no field {$from} for slug");
                }
                $clone->attr('from', &$this->_fields[$from]);
            }
            $this->_fields[$name] =& $clone;
        }
    } // }}}

    function setValue($name, $value) // {{{
    {
        if ($field = $this->_getField($name))
        {
            if ($field->attr('read_only'))
            {
                minim('log')->debug("$name field is read-only");
                return FALSE;
            }
            $ret = $field->setValue($value);
            if ($ret === FALSE)
            {
                minim('log')->debug("Couldn't set $name to ".print_r($value, TRUE));
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

    function save() // {{{
    {
        minim('orm')->{$this->_name}->save($this);
    } // }}}

    function delete() // {{{
    {
        minim('orm')->{$this->_name}->delete($this);
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
        $clone =& new $class($this->_type, array_filter($this->_attrs));
        return $clone;
    } // }}}

    function setValue($value) // {{{
    {
        if (!$this->attr('read_only'))
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

    function attr($name, $value=NULL) // {{{
    {
        if ($value !== NULL)
        {
            $this->_attrs[$name] = $value;
        }
        else if (array_key_exists($name, $this->_attrs))
        {
            return $this->_attrs[$name];
        }
        return NULL;
    } // }}}

    function isValid() // {{{
    {
        if ($this->_value === NULL and ($this->attr('not_null') or
                                        $this->attr('required')))
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
        if ($this->attr('autoincrement') and is_null($this->_value))
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
        $maxlen = $this->attr('maxlength');
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
        $maxlen = $this->attr('maxlength');
        $len = strlen($this->_value);
        if ($maxlen and $len > $maxlen)
        {
            return FALSE;
        }
        if ($this->attr('required') and $len < 1)
        {
            return FALSE;
        }

        return parent::isValid();
    } // }}}
} // }}}

class Minim_Orm_Slug extends Minim_Orm_Field // {{{
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
        $from = $this->attr('from');
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
        $from = $this->attr('from');
        if (!$value and $from)
        {
            return $this->_slugify($from->getValue());
        }
        return $value;
    } // }}}

    function isValid() // {{{
    {
        $from = $this->attr('from');
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
        if ($this->attr('auto_now'))
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
            if (!$params)
            {
                $params = array();
            }
            $this->_fields[$field_name] =& new $field['class']($name, $params);
            return $this;
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function &get($id) // {{{
    {
        $mp = new Minim_Orm_ModelProxy($this->_model, $id);
        return $mp;
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

class Minim_Orm_ModelProxy // {{{
{
    var $_model;
    var $_id;
    var $_cache;
    
    function Minim_Orm_ModelProxy($model, $id) // {{{
    {
        $this->_model = minim('orm')->{$model};
        $this->_id = $id;
        $this->_cache = NULL;
    } // }}}

    function &__get($name) // {{{
    {
        if (array_key_exists($name, $this->_model->_fields))
        {
            if (!$this->_cache)
            {
                $this->_cache =& $this->_model
                    ->filter(array('id__eq' => $this->_id))
                    ->first;
            }
            $ret = $this->_cache->{$name};
            return $ret;
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}
} // }}}

class Minim_Orm_ModelSet implements Iterator, Countable // {{{
{
    var $_model;
    var $_filters;
    var $_sorting;
    var $_start;
    var $_num;
    var $_cache;
    var $_count;
    var $_filter_classes;

    function Minim_Orm_ModelSet($model) // {{{
    {
        $this->_table = minim('orm')->{$model}->table();
        $this->_model = $model;
        $this->_filters = array();
        $this->_sorting = array();
        $this->_start = 0;
        $this->_num = 0;
        $this->_cache = array();
        $this->_count = NULL;
        $this->_filter_classes = $this->get_filter_classes();
        $this->_iterator_position = 0;
    } // }}}

    function filter($kwargs=array()) // {{{
    {
        foreach ($kwargs as $key => $arg)
        {
            // can't check whether model contains specified fields until we
            // instantiate, but waiting til then might be good anyway.
            // filters are ANDed together
            list($field, $op) = explode('__', $key);
            $class = $this->_class_for($op, $arg);
            $this->_filters[] =& new $class($field, $op, $arg);
        }
        return $this;
    } // }}}

    function order_by() // {{{
    {
        $args = func_get_args();
        foreach ($args as $arg)
        {
            preg_match('/^([-+?])([a-zA-Z0-9_]+)$/', $arg, $m);
            if ($m)
            {
                $direction = $m[1];
                $field = $m[2];
            }
            else
            {
                $direction = '+';
                $field = $arg;
            }
            $this->_sorting[] = array($field, $direction);
        }
        return $this;
    } // }}}

    function limit($a, $b=0) // {{{
    {
        if ($b)
        {
            $this->_start = $a;
            $this->_num = $b;
        }
        else
        {
            $this->_start = 0;
            $this->_num = $a;
        }
        return $this;
    } // }}}

    function get_filter_classes() // {{{
    {
        static $classes = array();
        if (!$classes)
        {
            // find all available filter types
            foreach (get_declared_classes() as $class)
            {
                if (is_subclass_of($class, 'Minim_Orm_Filter'))
                {
                    $func = array($class, "register");
                    $reg = call_user_func($func);
                    foreach ($reg as $op => $class)
                    {
                        $classes[$op] = $class;
                    }
                }
            }
        }
        return $classes;
    } // }}}

    function _class_for($op, $arg) // {{{
    {
        // find filter class for $key
        $classes = $this->get_filter_classes();
        if (!array_key_exists($op, $classes))
        {
            return 'Minim_Orm_Filter';
        }
        return $classes[$op];
    } // }}}

    function count() // {{{
    {
        if (is_null($this->_count))
        {
            list($query, $params) = $this->build_count_query();
            $s = $this->execute_query($query, $params);
            $row = $s->fetch();
            if (!($this->_count = @$row['_total']))
            {
                $this->_count = 0;
            }
        }
        return $this->_count;
    } // }}}

    function _fill_cache() // {{{
    {
        list($query, $params) = $this->build_query();
        $s = $this->execute_query($query, $params);
        $this->_cache = $this->_results_to_objects($s);
    } // }}}

    var $_max_existing = array();

    function _disambiguate_params($params, $fparams, $fquery) // {{{
    {
        $fkeys = array_keys($fparams);
        $pkeys = array_keys($params);
        foreach ($fkeys as &$key)
        {
            if (in_array($key, $pkeys))
            {
                if (!array_key_exists($key, $this->_max_existing))
                {
                    $this->_max_existing[$key] = 0;
                }
                $this->_max_existing[$key]++;
                $new_key = "{$key}{$this->_max_existing[$key]}";
                $fquery = str_replace($key, $new_key, $fquery);
                $fparams[$new_key] = $fparams[$key];
                unset($fparams[$key]);
            }
        }
        return array($fquery, $fparams);
    } // }}}

    function build_count_query() // {{{
    {
        return $this->build_query(True);
    } // }}}

    function build_query($count=False) // {{{
    {
        // intended to be overridden to allow use of alternative backends
        $query = array();
        $params = array();
        $this->_max_existing = array();
        foreach ($this->_filters as &$filter)
        {
            // TODO - hide this from the developer
            list($fquery, $fparams) = $this->_disambiguate_params($params,
                $filter->params(), $filter->to_string());
            $query[] = $fquery;
            $params = array_merge($params, $fparams);
        }
        // TODO - extend to allow OR
        $query = join(' AND ', $query);
        $fields = '*';
        if ($count)
        {
            $fields = 'COUNT(*) AS _total';
        }
        $sql = <<<SQL
            SELECT {$fields}
            FROM {$this->_table}
SQL;
        if ($query)
        {
            $sql .= <<<SQL
            WHERE {$query}
SQL;
        }
        if (!$count)
        {
            $sorting = array();
            foreach ($this->_sorting as $order_by)
            {
                list($field, $direction) = $order_by;
                if ($direction == '+')
                {
                    $direction = 'ASC';
                }
                if ($direction == '-')
                {
                    $direction = 'DESC';
                }
                // TODO - implement random sort
                $sorting[] = "$field $direction";
            }
            if ($sorting)
            {
                $sorting = join(', ', $sorting);
                $sql .= <<<SQL
                ORDER BY {$sorting}
SQL;
            }
            if ($this->_num)
            {
                $sql .= ' LIMIT ';
                if ($this->_start)
                {
                    $sql .= "{$this->_start}, ";
                }
                $sql .= $this->_num;
            }
        }
        $sql = trim(preg_replace('/\s+/', ' ', $sql));
        return array($sql, $params);
    } // }}}

    function execute_query($query, $params) // {{{
    {
        // intended to be overridden to allow use of alternative backends
        $s = minim('db')->prepare($query);
        $s->execute($params);
        return $s;
    } // }}} 

    function _results_to_objects($s) // {{{
    {
        $objects = array();
        $model =& minim('orm')->{$this->_model};
        foreach ($s->fetchAll() as $row)
        {
            $objects[] =& $model->from($row);
        }
        return $objects;
    } // }}}

    function __get($name) // {{{
    {
        // reveal intent
        if ($name == 'first')
        {
            if (!$this->_cache)
            {
                $this->_fill_cache();
            }
            if (sizeof($this->_cache) < 1)
            {
                return NULL;
            }
            return $this->_cache[0];
        }
    } // }}}

    function to_array() // {{{
    {
        $a = array();
        foreach ($this->items as $item)
        {
            $a[] = $item->to_array();
        }
        return $a;
    } // }}}

    // iterator methods
    var $_iterator_position;
    function &current() // {{{
    {
        return $this->_cache[$this->_iterator_position];
    } // }}}

    function key() // {{{
    {
        return $this->_iterator_position;
    } // }}}

    function next() // {{{
    {
        $this->_iterator_position++;
    } // }}}

    function rewind() // {{{
    {
        $this->_iterator_position = 0;
    } // }}}

    function valid() // {{{
    {
        if (!$this->_cache)
        {
            $this->_fill_cache();
        }
        if (sizeof($this->_cache) < 1)
        {
            return FALSE;
        }
        return $this->_iterator_position < count($this->_cache);
    } // }}}
} // }}}

class Minim_Orm_Filter // {{{
{
    function register() // {{{
    {
        // static method, should be overridden
        // returns an array of associative arrays in the form:
        // array(<string> => <classname>)
        // where <string> is the filter query keyword pattern
        $ops = array(
            'eq',
            'ne',
            'gt',
            'gte',
            'lt',
            'lte',
            'range',
        );
        return array_combine($ops, array_fill(0, count($ops), 'Minim_Orm_Filter'));
    } // }}}

    function Minim_Orm_Filter($field, $op, $arg) // {{{
    {
        $this->field = $field;
        $this->operator = $op;
        $this->value = $arg;
    } // }}}

    function to_string() // {{{
    {
        $ops = array(
            'eq' => '%s = :%s',
            'ne' => 'NOT (%s = :%s)',
            'gt' => '%s > :%s',
            'gte' => '%s >= :%s',
            'lt' => '%s < :%s',
            'lte' => '%s <= :%s',
            'range' => '%s BETWEEN :%s AND :%s',
        );
        if ($this->operator == 'range')
        {
            return sprintf($ops[$this->operator], $this->field, 'from', 'to');
        }
        return str_replace('%s', $this->field, $ops[$this->operator]);
    } // }}}

    function params() // {{{
    {
        if ($this->operator == 'range' and is_array($this->value))
        {
            // this is a range value
            return array(":from" => $this->value[0],
                         ":to" => $this->value[1]);
        }
        return array(":{$this->field}" => $this->value);
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
    function &_available_models() // {{{
    {
        if (!$this->_models)
        {
            $pat = '/minim\(([\'"])orm\1\)\s*->register_model\(\s*([\'"])'.
                   '([a-zA-Z]+)'.
                   '\2\s*\)/xm';
            
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
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    // database creation methods
    function create_database_tables() // {{{
    {
        foreach ($this->_available_models() as $name => $file)
        {
            $model = $this->{$name};
            $fields = array();
            foreach ($model->_fields as $name => $field)
            {
                $not_null = $field->attr('not_null') ? 'NOT NULL' : '';
                $auto_incr = $field->attr('autoincrement') ? ' AUTO_INCREMENT' : '';
                $primary_key = $name == 'id' ? ' PRIMARY KEY' : '';
                $type = '';
                switch ($field->_type)
                {
                    case 'int':
                        $type = 'INTEGER';
                        break;
                    case 'text':
                    case 'slug':
                        $type = 'TEXT';
                        if ($max_length = $field->attr('maxlength'))
                        {
                            $type = "VARCHAR($max_length)";
                        }
                        break;
                    case 'timestamp':
                        $type = 'DATETIME';
                        break;
                    default:
                        die("Unknown field type ({$field->_type}");
                }
                $fields[] = "`$name` $type $not_null $auto_increment $primary_key";
            }
            $fields = join(', ', $fields);
            $sql = <<<SQL
CREATE TABLE `{$model->_table}` ($fields) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
            
            // execute sql
        }
    } // }}}
} // }}}
