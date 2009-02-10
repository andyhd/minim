<?php
/**
 * Object Relational Mapper class.
 *
 * Keeps track of model Managers, model field types and backends.
 */
class Minim_Orm implements Minim_Plugin // {{{
{

    var $_managers;
    var $_field_types;
    var $model_paths;
    var $_backend;
    var $backend_paths;
    
    function Minim_Orm() // {{{
    {
        $this->_managers = array();
        $this->_field_types = array(
            'int' => array('class' => 'Minim_Orm_Integer',
                           'file' => realpath(__FILE__)),
            'text' => array('class' => 'Minim_Orm_Text',
                            'file' => realpath(__FILE__))
            // TODO - add more default field types
        );
        $this->model_paths = array();
        $this->_backend = NULL;
        $this->backend_paths = array(
            realpath(join(DIRECTORY_SEPARATOR, array(
                dirname(__FILE__), 'backends'
            )))
        );
    } // }}}

    /**
     * Register a model if it isn't already and return a reference to it's
     * Manager.
     *
     * @param string $name Name of model to register
     */
    function &register($name) // {{{
    {
        if (array_key_exists($name, $this->_managers))
        {
            throw new Minim_Orm_Exception("$name model already registered");
        }
        $this->_managers[$name] = new Minim_Orm_Manager($name, $this);
        return $this->_managers[$name];
    } // }}}

    /**
     * Get a reference to the Manager for the named model.
     * Managers are lazy-loaded.
     */
    function &__get($name) // {{{
    {
        if (!array_key_exists($name, $this->_managers))
        {
            // try to load the manager definition
            if (!$this->_load_model_definition($name))
            {
                throw new Minim_Orm_Exception("$name model not found");
            }
        }
        return $this->_managers[$name];
    } // }}}

    /**
     * Load definition for named model.
     */
    function _load_model_definition($name) // {{{
    {
        static $already_included = array();

        // don't do any work if we don't have to
        if (array_key_exists($name, $this->_managers))
        {
            return TRUE;
        }

        // look for the model registration method call
        $pattern = '/->register\(([\'"])'.$name.'\1/';

        // scan the include paths
        foreach ($this->model_paths as $path)
        {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file)
            {
                $filename = $file->getPathname();
                if (substr($filename, -4) == '.php'
                    and !in_array($filename, $already_included)
                    and preg_match($pattern, file_get_contents($filename), $m))
                {
                    include $filename;

                    // don't include the file again
                    $already_included[] = $filename;

                    // break out of loops
                    return array_key_exists($name, $this->_managers);
                }
            }
        }

        // we didn't find the definition
        return FALSE;
    } // }}}

    /**
     * Register a field type
     */
    function register_field_type($type, $file, $class_name) // {{{
    {
        $this->_field_types[$type] = array(
            'file' => $file,
            'class' => $class_name
        );
    } // }}}

    /**
     * Set the backend for the ORM, eg: MySQL DB, SQLite DB, etc
     * Return FALSE on failure
     */
    function &set_backend($type, $params) // {{{
    {
        // do not load a second backend
        if ($this->_backend)
        {
            return TRUE;
        }

        $pattern = '/^class[\W]+(\w+)\W+implements[^\{]*Minim_Orm_Backend/ms';
        foreach ($this->backend_paths as $path)
        {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $file)
            {
                $filename = $file->getPathname();
                if (substr($filename, -4) == '.php'
                    and $type == substr($file->getFilename(), 0, -4)
                    and preg_match($pattern, file_get_contents($filename), $m))
                {
                    include $filename;

                    $class = $m[1];

                    $this->_backend = new $class($params, $this);

                    // break out of loops
                    return $this->_backend;
                }
            }
        }

        // we didn't find the backend
        return FALSE;
    } // }}}
} // }}}

interface Minim_Orm_Backend {}

class Minim_Orm_Exception extends Exception {}

/**
 * Model Manager class.
 *
 * Primary interface with models. Provides model definition and manipulation
 * methods.
 */
class Minim_Orm_Manager // {{{
{

    var $_orm;
    var $_model;
    var $_db_table;
    var $_fields;
    var $_sorting;

    function Minim_Orm_Manager($name, &$orm) // {{{
    {
        $this->_model = $name;
        $this->_orm =& $orm;
        $this->_db_table = strtolower($name);
        $this->_fields = array();
        $this->_sorting = array();
    } // }}}

    /**
     * Enable syntactic sugar for adding fields to model.
     */
    function &__call($name, $args) // {{{
    {
        if (array_key_exists($name, $this->_orm->_field_types))
        {
            list($field_name, $params) = $args;
            if (is_null($params))
            {
                $params = array();
            }
            $this->add_field($name, $field_name, $params);
            return $this;
        }
        return $this;
    } // }}}

    /**
     * Add a field definition to a model
     */
    function add_field($type, $name, $params) // {{{
    {
        $types = $this->_orm->_field_types;
        if (array_key_exists($type, $types))
        {
            // check class loaded
            if (!class_exists($types[$type]['class']))
            {
                require_once $types[$type]['file'];
            }

            // instantiate field object
            $params += array(
                'manager' => $this,
                'name' => $name
            );
            $this->_fields[$name] = new $types[$type]['class']($params);
        }
    } // }}}

    /**
     * Enable syntactic sugar for access fields by name
     */
    function __get($name) // {{{
    {
        if (array_key_exists($name, $this->_fields))
        {
            return $this->_fields[$name];
        }
    } // }}}

    /**
     * Create a new DataObject instance based on this model
     */
    function &create($data=array()) // {{{
    {
        $instance = new Minim_Orm_DataObject($this);

        if ($data)
        {
            foreach ($this->_fields as $name => $field)
            {
                if (array_key_exists($name, $data))
                {
                    $instance->$name = $data[$name];
                }
            }
        }

        return $instance;
    } // }}}

    /**
     * Save specified dataobject to ORM backend
     */
    function save(&$do) // {{{
    {
        $this->_orm->_backend->save($do, $this);
    } // }}}

    /**
     * Delete specified dataobject from ORM backend
     */
    function delete(&$do) // {{{
    {
        $this->_orm->_backend->delete($do, $this);
    } // }}}

    /**
     * Retrieve a single data object from the ORM backend
     */
    function &get($params) // {{{
    {
        return $this->_orm->_backend->get($params, $this);
    } // }}}

    /**
     * Fetch all model instances from ORM backend
     */
    function all() // {{{
    {
        return new Minim_Orm_ModelSet($this);
    } // }}}

    /**
     * Get a QueryObject on the 'all' ModelSet
     */
    function &where($fieldname) // {{{
    {
        return $this->all()->where($fieldname);
    } // }}}
} // }}}

/**
 * Represents a field in model.
 *
 * Intended to be subclassed to provide validation.
 */
class Minim_Orm_Field // {{{
{
    /**
     * Validate field value.
     */
    function accepts_value($value) // {{{
    {
        return TRUE;
    } // }}}
} // }}}

/**
 * A set of model instances.
 *
 * Represents the result set of a query. Uses lazy evaluation on iteration.
 */
class Minim_Orm_ModelSet implements Iterator, Countable // {{{
{
    var $_manager;
    var $_iterator_position;
    var $_filters;
    var $_sorting;
    var $_count;
    var $_cache;

    function Minim_Orm_ModelSet(&$manager) // {{{
    {
        $this->_manager =& $manager;
        $this->_iterator_position = 0;
        $this->_filters = array();
        $this->_sorting = array();
        $this->_cache = array();
    } // }}}

    /**
     * Get a query object to apply to this modelset
     */
    function &where($fieldname) // {{{
    {
        return $this->_filter($fieldname);
    } // }}}

    /**
     * @access private
     */
    function &_filter($field_name, $conjunction='AND') // {{{
    {
        $qo = new Minim_Orm_QueryObject($field_name, $conjunction, $this);
        $this->_filters[] =& $qo;
        return $qo;
    } // }}}

    /**
     * Syntactic sugar for building filter chain
     */
    function &__call($name, $params) // {{{
    {
        if ($name == 'and')
        {
            return $this->_filter($params[0]);
        }
        elseif ($name == 'or')
        {
            return $this->_filter($params[0], 'OR');
        }
    } // }}}

    /**
     * Sort the modelset by the specified field(s)
     */
    function order_by() // {{{
    {
        $args = func_get_args();
        error_log('order_by args:'.print_r($args, TRUE));
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

    /**
     * Limit the modelset to a specified number or range.
     */
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

    /**
     * Get the number of model instances in the modelset
     */
    function count() // {{{
    {
        if (is_null($this->_count))
        {
            $backend =& $this->_manager->_orm->_backend;
            $this->_count = $backend->count_dataobjects($this);
        }
        return $this->_count;
    } // }}}

    /**
     * @access private
     */
    function _fill_cache() // {{{
    {
        $backend =& $this->_manager->_orm->_backend;
        $this->_cache =& $backend->get_dataobjects($this);
    } // }}}

    /**
     * Enable syntactic sugar for first model instance in the modelset
     */
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

    /**
     * Return the model instances in the modelset as an array
     */
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
    /**
     * Get model instance at the current iterator position
     */
    function &current() // {{{
    {
        return $this->_cache[$this->_iterator_position];
    } // }}}

    /**
     * Increment the iterator position
     */
    function next() // {{{
    {
        $this->_iterator_position++;
    } // }}}

    /**
     * Get the current iterator position
     */
    function key() // {{{
    {
        return $this->_iterator_position;
    } // }}}

    /**
     * Return the iterator to the start of the modelset
     */
    function rewind() // {{{
    {
        $this->_iterator_position = 0;
    } // }}}

    /**
     * Check if there are more model instances in the modelset
     */
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

/**
 * Data Object class.
 *
 * Represents a model instance.
 */
class Minim_Orm_DataObject // {{{
{
    var $_manager;
    var $_in_db;
    var $_data;

    function Minim_Orm_DataObject(&$manager) // {{{
    {
        $this->_manager = $manager;
        $this->_in_db = FALSE;
        $this->_data = array();

        // instantiate data structure
        foreach ($this->_manager->_fields as $name => $field)
        {
            $this->_data[$name] = NULL;
        }
    } // }}}

    /**
     * Enable syntactic sugar for assigning field values
     */
    function __set($name, $value) // {{{
    {
        if (array_key_exists($name, $this->_data))
        {
            if ($this->_manager->_fields[$name]->accepts_value($value))
            {
                return $this->_data[$name] = $value;
            }
            else
            {
                throw new Minim_Orm_Exception(
                    "Value (".var_export($value, TRUE).") is not accepted by ".
                    get_class($this->_manager->_fields[$name])
                );
            }
        }
    } // }}}

    /**
     * Enable syntactic sugar for accessing field values
     */
    function __get($name) // {{{
    {
        if (array_key_exists($name, $this->_data))
        {
            return $this->_data[$name];
        }
    } // }}}

    /**
     * Save object to ORM backend
     */
    function save() // {{{
    {
        $this->_manager->save($this);
    } // }}}

    /**
     * Delete object
     */
    function delete() // {{{
    {
        $this->_manager->delete($this);
    } // }}}
} // }}}

/**
 * Query objects
 *
 * Represents a modelset filter expression.
 *
 * Provides syntax like:
 * <code>
 * $posts = $orm->post->all();
 * $posts->where("field")->gt($x)
 * $posts->where("field")->in_range($x, $y)
 * $posts->where("field")->equals($x)->and("field")->notequals($y);
 * $posts->where("field")->lt($x)->or("field")->gt($y);
 * </code>
 */
class Minim_Orm_QueryObject // {{{
{
    var $_field;
    var $_and;
    var $_modelset;
    var $_operator;
    var $_operand;

    function Minim_Orm_QueryObject($fieldname, $conjunction, &$modelset) // {{{
    {
        $this->_and = $conjunction == 'AND';
        $this->_field = $fieldname;
        $this->_modelset =& $modelset;
        $this->_operator = '';
        $this->_operand = '';
    } // }}}

    /**
     * Enable syntactic sugar for defining criteria
     */
    function &__call($name, $params) // {{{
    {
        switch ($name)
        {
            case 'equals':
                $this->_operator = '=';
                $this->_operand = $params[0];
                break;
            case 'notequals':
                $this->_operator = '!=';
                $this->_operand = $params[0];
                break;
            case 'gt':
                $this->_operator = '>';
                $this->_operand = $params[0];
                break;
            case 'lt':
                $this->_operator = '<';
                $this->_operand = $params[0];
                break;
            case 'gte':
                $this->_operator = '>=';
                $this->_operand = $params[0];
                break;
            case 'lte':
                $this->_operator = '<=';
                $this->_operand = $params[0];
                break;
            case 'in_range':
                $this->_operator = 'range';
                $this->_operand = array($params[0], $params[1]);
                break;
        }
        return $this->_modelset;
    } // }}}
} // }}}

class Minim_Orm_Integer extends Minim_Orm_Field // {{{
{
    function accepts_value($value) // {{{
    {
        return is_int($value);
    } // }}}
} // }}}

class Minim_Orm_Text extends Minim_Orm_Field // {{{
{
    function accepts_value($value) // {{{
    {
        return is_string($value);
    } // }}}
} // }}}
