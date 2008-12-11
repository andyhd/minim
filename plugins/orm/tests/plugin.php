<?php
require 'minim/plugins/tests/tests.php';
require 'minim/plugins/orm/plugin.php';

function orm()
{
    static $instance;
    if (!$instance)
    {
        $instance = new Minim_Orm();
    }
    return $instance;
}

class Minim_Orm_TestCase extends TestCase
{
    function test_register_model()
    {
        $this->assertEqual(count(orm()->_managers), 0);
        orm()->model_paths[] = realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        )));

        // check lazy loading - no managers yet
        $this->assertEqual(count(orm()->_managers), 0);

        $manager = orm()->dummy;
        $this->assertEqual(count(orm()->_managers), 1);
        $this->assertEqual($manager->_model, "dummy");
    }

    function test_register_existing_model()
    {
        $this->assertTrue(array_key_exists('dummy', orm()->_managers));
        $this->assertException('Minim_Orm_Exception',
            'orm()->register("dummy");');
    }

    function test_access_unregistered_manager()
    {
        $this->assertTrue(!array_key_exists('foo', orm()->_managers));
        $this->assertException('Minim_Orm_Exception', 'orm()->foo;');
    }

    function test_model_definition_add_field()
    {
        $manager = orm()->dummy;
        $this->assertEqual(count($manager->_fields), 0);

        // set up test field type
        orm()->register_field_type('dummy', realpath(join(DIRECTORY_SEPARATOR,
            array(dirname(__FILE__), 'test_field_type.php'))), 'Dummy');

        $manager->dummy('dummy', array('auto_increment' => TRUE));
        $this->assertEqual(count($manager->_fields), 1,
            "Unexpected number of manager fields (".count($manager->_fields).")");
        $this->assertEqual(get_class($manager->dummy), 'Dummy',
            "Unexpected field type (".get_class($manager->dummy).")");
    }

    function test_data_object()
    {
        $manager = orm()->dummy;
        $do = $manager->create();
        $this->assertTrue($do instanceof Minim_Orm_DataObject);

        $do->dummy = 'testing';
        $this->assertEqual($do->dummy, 'testing');
    }

    function test_orm_backend()
    {
        $db =& new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $res = $db->exec("CREATE TABLE dummy (dummy TEXT)");
        orm()->set_backend($db);
        $do = orm()->dummy->create();
        $do->dummy = 'testing';
        $do->save();
        $sth = $db->prepare('SELECT * FROM dummy');
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($result['dummy'], 'testing');
    }

    function test_data_object_get()
    {
        $do = orm()->dummy->get(array('dummy' => 'testing'));
        $this->assertEqual($do->dummy, 'testing');
    }
}

$test = new Minim_Orm_TestCase();
dump_results($test->run());
