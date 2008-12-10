<?php
/**
 * An Object Relational Mapping plugin.
 *
 * Implements Data Objects, Managers, Query Objects/Model Sets, lazy loading
 *
 * <b>Registering a new model:</b>
 * 
 * <code>
 *   minim('orm')->register_model($model_name)
 *               ->table($table_name)
 *               ->default_sort($order)
 *               ->{$field_type}($name, $params_array)
 *               ->foreign_key($name, $params_array)
 *               ...;
 * </code>
 *
 * <b>Querying database - get a list of models:</b>
 *
 * <code>
 *   $models = minim('orm')->{$model}
 *                         ->all();
 *
 *   $models = minim('orm')->{$model}
 *                         ->filter($criteria_array)
 *                         ...
 *                         ->order_by($order)
 *                         ->limit($limit);
 * </code>
 *
 * <b>Accessing model values:</b>
 *
 * <code>
 *   $value = minim('orm')->{$model}
 *                        ->get($id)
 *                        ->{$field_name};
 * </code>
 *
 * <b>Saving / Deleting models:</b>
 *
 * <code>
 *   minim('orm')->{$model}
 *               ->from($data_array)
 *               ->save();
 *
 *   minim('orm')->{$model}
 *               ->get($id)
 *               ->delete();
 * </code>
 *
 * <b>Create database tables from models</b>
 *
 * <code>
 *   minim('orm')->create_database_tables();
 * </code>
 **/
class Minim_Orm implements Minim_Plugin // {{{
{

    /**#@+
     * @access private
     */
    var $_models;
    var $_managers;
    var $_field_types;
    /**#@-*/

    /**#@+
     * @var array
     */
    var $model_paths;
    var $field_type_paths;
    /**#@-*/

    function Minim_Orm() // {{{
    {
        $this->_models = array();
        $this->_managers = array();
        $this->_field_types = array();
        $this->model_paths = array();
        $this->field_type_paths = array(
            realpath(dirname(__FILE__))
        );
    } // }}}

    // model construction methods
    /** @access private */
    function &_available_field_types() // {{{
    {
        if (!$this->_field_types)
        {
            // get a list of available field_types
            $pat = '/class\s+([^\s]+)\s+extends\s+Minim_Orm_Field/m';
            $matches = minim()->grep($pat, $this->_field_type_paths);
            if (!$matches)
            {
                error_log("No field type definitions found");
                return $this->_field_types;
            }
            foreach ($matches as $match)
            {
                foreach ($match['matches'][1] as $class)
                {
                    $type = strtolower(substr($class, 10));
                    $this->_field_types[$type] = array(
                        'file' => $match['file'],
                        'class' => $class
                    );
                }
            }
            error_log("ORM field types available: ".
                print_r(array_keys($this->_field_types), TRUE));
        }
        return $this->_field_types;
    } // }}}

    /** @access private */
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

    /** @ignore */
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
            $this->_managers[$model] =& new Minim_Orm_Manager($model, $this);
        }
        return $this->_managers[$model];
    } // }}}

    // query methods
    /* @access private */
    function &_available_models() // {{{
    {
        if (!$this->_models)
        {
            $pat = '/minim\(([\'"])orm\1\)\s*->register_model\(\s*([\'"])'.
                   '([a-zA-Z]+)'.
                   '\2\s*\)/xm';
            
            // check each model file
            $matches = minim()->grep($pat, $this->_model_paths);
            foreach ($matches as $match)
            {
                foreach ($match['matches'][3] as $model)
                {
                    $this->_models[$model] = $match['file'];
                }
            }
            error_log("Models available: ".print_r(array_keys($this->_models),
                                                   TRUE));
        }
        return $this->_models;
    } // }}}

    /** @ignore */
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
                include $this->_models[$name];
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
    function create_database_table($model) // {{{
    {
        if (array_key_exists($model, $this->_available_models()))
        {
            $model = $this->{$model};
            $fields = array();
            foreach ($model->_fields as $name => $field)
            {
                $not_null = $field->attr('not_null') ? 'NOT NULL' : '';
                $auto_incr = $field->attr('auto_increment') ? ' AUTO_INCREMENT' : '';
                $primary_key = $field->attr('primary_key') ? ' PRIMARY KEY' : '';
                $type = '';
                switch ($field->_type)
                {
                    case 'int':
                    case 'foreign_key':
                        // TODO - allow non-integer foreign keys
                        $type = 'INTEGER';
                        break;
                    case 'text':
                    case 'slug':
                        $type = 'TEXT';
                        if ($max_length = $field->attr('max_length'))
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
                $fields[] = "`$name` $type $not_null $auto_incr $primary_key";
            }
            $fields = join(', ', $fields);
            $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `{$model->_table}` ($fields) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;

            // execute sql
            $s = minim('db')->prepare($sql);
            $s->execute();
        }
    } // }}}
} // }}}

/**
 * A DataObject is a flyweight value object, it should use the least memory
 * possible. All manipulation of DataObjects is via their Manager.
 */
class Minim_DataObject // {{{
{
    var $_manager;
    var $_fields;
    var $_in_db;

    function Minim_DataObject(&$manager) // {{{
    {
        $this->_manager =& $manager;
        $this->_fields = array();
        $this->_in_db = FALSE;
    } // }}}

    function set($name, $value) // {{{
    {
        if (array_key_exists($name, $this->_manager->_fields))
        {
            return $this->_fields[$name] = $value;
        }

        throw new Minim_DataObject_Exception(
            "Can't set {$this->_manager->_model} field $name: does not exist");
    } // }}}

    function __set($name, $value) // {{{
    {
        return $this->set($name, $value);
    } // }}}

    function get($name) // {{{
    {
        if (array_key_exists($name, $this->_manager->_fields) and
            array_key_exists($name, $this->_fields))
        {
            return $this->_fields[$name];
        }
        throw new Minim_DataObject_Exception(
            "Can't get {$this->_manager->_model} field $name: does not exist");
    } // }}}

    function __get($name) // {{{
    {
        return $this->get($name);
    } // }}}

    function save() // {{{
    {
        $this->_in_db = $this->_manager->save($this);
    } // }}}

    function delete() // {{{
    {
        $this->_in_db = !$this->_manager->delete($this);
    } // }}}

    function to_array() // {{{
    {
        return $this->_fields;
    } // }}}

    function is_valid() // {{{
    {
        return $this->_manager->validate($this);
    } // }}}
} // }}}

class Minim_Orm_Manager // {{{
{
    var $_orm;
    var $_model;
    var $_table;
    var $_pk;
    var $_fields;
    var $_default_sort;

    function Minim_Orm_Manager($model, &$orm) // {{{
    {
        $this->_orm = $orm;
        $this->_model = $model;
        $this->_fields = array();
        $this->_table = NULL;
        $this->_pk = NULL;
        $this->_default_sort = NULL;
    } // }}}

    /**
     * Model database table name getter/setter
     */
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

    /**
     * Enable field type name syntax for constructing and adding fields
     * Eg: $manager->text($field_name, $params);
     *     $manager->int($field_name, $params);
     */
    function &__call($name, $args) // {{{
    {
        // if there is a field type by this name
        $field_types = $this->_orm->_available_field_types();
        $key = strtolower($name);
        if (array_key_exists($key, $field_types))
        {
            // include the file
            $field =& $field_types[$key];
            require_once $field['file'];

            // first argument is always the field name
            $field_name = array_shift($args);

            // pass in the parameters
            $params = array_shift($args);
            if (!$params)
            {
                $params = array();
            }

            // check for primary key
            if (array_key_exists('primary_key', $params))
            {
                $this->_pk = $field_name;
            }

            // get an instance of the field class
            $this->_fields[$field_name] =& new $field['class']($name, $params);
            return $this;
        }

        // no field type with this name
        throw new Minim_DataObject_Exception("Field type $name not found");
    } // }}}

    /**
     * Fetch a single record from the model database table, with the given
     * primary key value
     */
    function &get($pk) // {{{
    {
        $mp = new Minim_Orm_ModelProxy($this->_model, $pk);
        return $mp;
    } // }}}

    /**
     * Default sort order getter/setter
     */
    function default_sort($sort = NULL) // {{{
    {
        if ($sort)
        {
            $this->_default_sort = $sort;
            return $this;
        }
        return $this->_default_sort;
    } // }}}

    /**
     * Fetch all records from model database table
     */
    function &all() // {{{
    {
        $ms = new Minim_Orm_ModelSet($this->_model);
        return $ms;
    } // }}}

    /**
     * Fetch all records from model database table that match given criteria
     */
    function &filter($kwargs=array()) // {{{
    {
        $ms = $this->all()->filter($kwargs);
        return $ms;
    } // }}}

    /**
     * Instantiate DataObject from array
     */
    function &from($data) // {{{
    {
        if (is_array($data))
        {
            // get blank instance
            $model =& new Minim_DataObject($this);

            // set values
            foreach ($data as $key => $value)
            {
                // this will throw an exception if $key is not a valid field
                $model->$key = $value;
            }
            error_log("Created {$this->_model} from ".print_r($data, TRUE));
            return $model;
        }
        throw new Minim_DataObject_Exception(
            "Can't create $this->_model from non-array");
    } // }}}

    /**
     * Delete a DataObject by primary key
     */
    function delete($instance) // {{{
    {
        $sql = "DELETE FROM {$this->_table} WHERE {$this->_pk}=:pk";
        $data = array(':pk' => $instance->get($this->_pk));
        $s = minim('db')->prepare($sql);
        return $s->execute($data);
    } // }}}

    /**
     * Save DataObject to database
     */
    function save(&$instance) // {{{
    {
        $updates = array();
        $data = array();

        error_log("Saving: ".print_r($instance, TRUE));

        // assume this is an INSERT
        $sql = "INSERT INTO {$this->_table} SET %s";
        $is_insert = TRUE;
        $auto_increment = FALSE;
        if ($this->_pk and $instance->get($this->_pk) === NULL and
            $this->_fields[$this->_pk]->attr('auto_increment'))
        {
            // the primary key is automatically set
            $auto_increment = TRUE;
        }

        foreach ($this->_fields as $name => &$field)
        {
            $value = $instance->get($name);
            if (!$field->attr('primary_key'))
            {
                if ($value === NULL and $field->attr('not_null'))
                {
                    // TODO - add hooks for null value handlers
#                    throw new Minim_DataObject_Exception(
#                        "{$this->_model} $name value cannot be NULL");
                }
                $updates[] = "$name = :$name";
                $data[":$name"] = $field->getValueForDb();
            }
        }

        $sql = sprintf($sql, join(', ', $updates));
        $s = minim('db')->prepare($sql);
        try
        {
            $ret = $s->execute($data);
        }
        catch (Exception $e)
        {
            if ($e->getCode() == '23000')
            {
                // primary key already exists, so this is an UPDATE
                $sql = "UPDATE {$this->_table} SET %s WHERE {$this->_pk}=:pk";
                $data[':pk'] = $instance->get($this->_pk);
                $s = minim('db')->prepare($sql);
                $ret = $s->execute($data);
                $is_insert = FALSE;
            }
            else
            {
                throw $e;
            }
        }

        if ($is_insert and $auto_increment)
        {
            error_log("Setting $pk of new {$this->_model} to ".
                      $ret['last_insert_id']); 
            $instance->set($this->_pk, $ret['last_insert_id']);
        }
        return $ret;
    } // }}}

    function validate(&$instance) // {{{
    {
        // TODO
        return TRUE;
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
        return $this->_value = $value;
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
} // }}}

class Minim_Orm_Int extends Minim_Orm_Field // {{{
{
    function setValue($value) // {{{
    {
        if ($value !== NULL)
        {
            if (!is_numeric($value))
            {
                throw new Minim_DataObject_Exception('Value must be numeric');
            }
            $value = (int) $value;
        }

        return parent::setValue($value);
    } // }}}

    function isValid() // {{{
    {
        if (is_int($this->_value))
        {
            return TRUE;
        }
        if ($this->attr('auto_increment') and is_null($this->_value))
        {
            return TRUE;
        }

        return parent::isValid();
    } // }}}
} // }}}

class Minim_Orm_Foreign_Key extends Minim_Orm_Field // {{{
{
    function setValue(&$value) // {{{
    {
        // if value is an object of the related type, store its id
        $model = $this->attr('model');
        if (is_object($value) and @$value->_name == $model)
        {
            $field = $this->attr('field');
            $key = @$value->$field;
            if ($key)
            {
                return parent::setValue($key);
            }
            else
            {
                throw new Minim_DataObject_Exception(
                    "FOREIGN KEY $field not found in $model");
            }
        }

        return parent::setValue($value);
    } // }}}

    function &getValue($as_model=FALSE) // {{{
    {
        $value = parent::getValue();
        if ($as_model)
        {
            $model = $this->attr('model');
            $field = $this->attr('field');
            if ($value and $model and $field)
            {
                $obj = minim('orm')->$model
                                   ->filter(array("{$field}__eq" => $value))
                                   ->first;
                return $obj;
            }
        }
        return $value;
    } // }}}

    function isValid() // {{{
    {
        if ($this->_value !== NULL)
        {
            return FALSE != $this->getValue(TRUE);
        }
        return parent::isValid();
    } // }}}
} // }}}

class Minim_Orm_Text extends Minim_Orm_Field // {{{
{
    function setValue($value) // {{{
    {
        if ($value !== NULL)
        {
            if (!is_string($value))
            {
                throw new Minim_DataObject_Exception(
                    'Value must be a string');
            }
            
            # TODO - add unicode support here (mb_strlen)
            $maxlen = $this->attr('max_length');
            if ($maxlen and strlen($value) > $maxlen)
            {
                # TODO - add unicode support here (mb_substr)
                $value = substr($value, 0, $maxlen);

                # Raise a WARNING?
            }
        }

        return parent::setValue($value);
    } // }}}

    function isValid() // {{{
    {
        // TODO - add unicode support (mb_strlen)
        $maxlen = $this->attr('max_length');
        $len = strlen($this->_value);
        if ($maxlen and $len > $maxlen)
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
        if ($value !== NULL and $from)
        {
            throw new Minim_DataObject_Exception(
                "Field value set automatically from $from");
        }
        if (is_string($value))
        {
            return parent::setValue($this->_slugify($value));
        }
        if ($value !== NULL)
        {
            throw new Minim_DataObject_Exception(
                "Value must be string");
        }
    } // }}}

    function getValue() // {{{
    {
        $value = parent::getValue();
        $from = $this->attr('from');
        if ($value === NULL and $from)
        {
            error_log("Slug field: ".print_r($this, TRUE));
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
                throw new Minim_DataObject_Exception(
                    "$value not recognized as date string");
            }
        }
        elseif (is_int($value))
        {
            if ($value < 0)
            {
                throw new Minim_DataObject_Exception(
                    "$value is out of range");
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

    function _fill_cache() // {{{
    {
        if (!$this->_cache)
        {
            $this->_cache = $this->_model
                                 ->filter(array('id__eq' => $this->_id))
                                 ->first;
        }
    } // }}}

    function &__get($name) // {{{
    {
        if (array_key_exists($name, $this->_model->_fields))
        {
            $this->_fill_cache();
            if ($this->_cache)
            {
                $ret = $this->_cache->{$name};
                return $ret;
            }
        }
        $nullVar = NULL;
        return $nullVar;
    } // }}}

    function __set($name, $value) // {{{
    {
        if (array_key_exists($name, $this->_model->_fields))
        {
            $this->_fill_cache();
            return $this->_cache->{$name} = $value;
        }
    } // }}}

    function __call($name, $args) // {{{
    {
        if ($name == 'save')
        {
            if ($this->_cache)
            {
                return $this->_cache->save();
            }
            return FALSE;
        }
        if ($name == 'delete')
        {
            return $this->_model->delete($this->_id);
        }
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
        if ($this->items)
        {
            foreach ($this->items as $item)
            {
                $a[] = $item->to_array();
            }
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

class Minim_DataObject_Exception extends Exception {}
