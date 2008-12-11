<?php
class Minim_Orm // {{{
{

    var $_managers;
    var $_field_types;
    var $model_paths;
    var $_backend;
    
    function Minim_Orm() // {{{
    {
        $this->_managers = array();
        $this->_field_types = array();
        $this->model_paths = array();
        $this->_backend = NULL;
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
        return $this->_managers[$name] = new Minim_Orm_Manager($name, $this);
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

    function register_field_type($type, $file, $class_name) // {{{
    {
        $this->_field_types[$type] = array(
            'file' => $file,
            'class' => $class_name
        );
    } // }}}

    function set_backend(&$backend) // {{{
    {
        $this->_backend = $backend;
    } // }}}
} // }}}

class Minim_Orm_Exception extends Exception {}

class Minim_Orm_Manager // {{{
{

    var $_orm;
    var $_model;
    var $_db_table;
    var $_fields;

    function Minim_Orm_Manager($name, &$orm) // {{{
    {
        $this->_model = $name;
        $this->_orm = $orm;
        $this->_db_table = strtolower($name);
        $this->_fields = array();
    } // }}}

    /**
     * Enable syntactic sugar for adding fields to model.
     */
    function __call($name, $args) // {{{
    {
        if (array_key_exists($name, $this->_orm->_field_types))
        {
            list($field_name, $params) = $args;
            $this->add_field($name, $field_name, $params);
        }
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
            $this->_fields[$name] =& new $types[$type]['class']($params);
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
    function &create($data=array())
    {
        $instance =& new Minim_Orm_DataObject($this);

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
    }

    /**
     * Save specified dataobject to ORM backend
     */
    function save($do) // {{{
    {
        $fields = array_keys($this->_fields);
        $values = preg_replace('/^/', ':', $fields);
        $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)',
            $this->_db_table, join(',', $fields), join(',', $values));
        $sth = $this->_orm->_backend->prepare($sql);
        $values = array_combine($values, array_values($do->_data));
        $sth->execute($values);
    } // }}}

    /**
     * Retrieve a single data object from the ORM backend
     */
    function get($params) // {{{
    {
        $criteria = '';
        foreach ($params as $key => $value)
        {
            if (strlen($criteria) > 0)
            {
                $criteria .= ' AND ';
            }
            $criteria .= "$key = :$key";
        }
        $sql = sprintf('SELECT * FROM %s WHERE %s',
            $this->_db_table, $criteria);
        $sth = $this->_orm->_backend->prepare($sql);
        $values = array_combine(
            preg_replace('/^/', ':', array_keys($params)),
            array_values($params)
        );
        $sth->execute($values);
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        $num_results = count($results);
        if ($num_results == 1)
        {
            $instance =& $this->create($results[0]);
            $instance->_in_db = TRUE;
            return $instance;
        }
        elseif ($num_results > 1)
        {
            throw new Minim_Orm_Exception("More than one result for get");
        }
        throw new Minim_Orm_Exception("No results for get");
    } // }}}
} // }}}

class Minim_Orm_Field // {{{
{
    function accepts_value($value) // {{{
    {
        return TRUE;
    } // }}}
} // }}}

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
} // }}}
