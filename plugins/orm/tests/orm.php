<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/orm/orm.php';

if (!function_exists('orm'))
{
    function &orm() // {{{
    {
        static $instance;
        if (!$instance)
        {
            $instance = new Minim_Orm();
        }
        return $instance;
    } // }}}
}

class Minim_Orm_TestCase extends TestCase // {{{
{
    function test_orm_reference() // {{{
    {
        $orm = orm();
        $this->assertTrue($orm === orm());
    } // }}}

    function test_register_model() // {{{
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
    } // }}}

    function test_register_existing_model() // {{{
    {
        $this->assertTrue(array_key_exists('dummy', orm()->_managers));
        $this->assertException('Minim_Orm_Exception',
            'orm()->register("dummy");');
    } // }}}

    function test_access_unregistered_manager() // {{{
    {
        $this->assertTrue(!array_key_exists('foo', orm()->_managers));
        $this->assertException('Minim_Orm_Exception', 'orm()->foo;');
    } // }}}

    function test_model_definition_add_field() // {{{
    {
        $orm = new Minim_Orm();
        $orm->register('blarch');
        $manager = $orm->blarch;
        $this->assertEqual(count($manager->_fields), 0);
        $manager->int('foo', array('auto_increment' => TRUE));
        $count = count($manager->_fields);
        $this->assertEqual($count, 1,
            "Unexpected number of manager fields ($count)");
        $this->assertEqual(get_class($manager->foo), 'Minim_Orm_Integer',
            "Unexpected field type (".get_class($manager->foo).")");
    } // }}}

    function test_model_definition_add_custom_field() // {{{
    {
        $manager = orm()->dummy;
        $this->assertEqual(count($manager->_fields), 0,
            "Expected no defined managers");

        // set up test field type
        orm()->register_field_type('dummy', realpath(join(DIRECTORY_SEPARATOR,
            array(dirname(__FILE__), 'res', 'test_field_type.php')
        )), 'Dummy');

        $manager->dummy('dummy', array('auto_increment' => TRUE));
        $count = count($manager->_fields);
        $this->assertEqual($count, 1,
            "Unexpected number of manager fields ($count)");
        $this->assertEqual(get_class($manager->dummy), 'Dummy',
            "Unexpected field type (".get_class($manager->dummy).")");
    } // }}}

    function test_data_object() // {{{
    {
        $manager = orm()->dummy;
        $do = $manager->create();
        $this->assertTrue($do instanceof Minim_Orm_DataObject);

        $do->dummy = 'testing';
        $this->assertEqual($do->dummy, 'testing');
    } // }}}

    function test_orm_backend() // {{{
    {
        orm()->set_backend('sqlite', array('database' => ':memory:'));
        $backend =& orm()->_backend;
        $backend->execute_query("CREATE TABLE dummy (dummy TEXT)", array());
        $do = orm()->dummy->create();
        $do->dummy = 'testing';
        $do->save();
        $sth = $backend->execute_query('SELECT * FROM dummy', array());
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($result['dummy'], 'testing');
    } // }}}

    function test_data_object_get() // {{{
    {
        $do = orm()->dummy->get(array('dummy' => 'testing'));
        $this->assertEqual($do->dummy, 'testing');
    } // }}}

    function test_data_object_filter() // {{{
    {
        $resultset = orm()->dummy->where('dummy')->equals('testing');
        $this->assertEqual(count($resultset), 1,
            "Expecting 1 result, got 0.");

        $resultset = orm()->dummy->where('dummy')->equals('foo');
        $count = count($resultset);
        $this->assertEqual($count, 0,
            "Expecting 0 results, got $count");

        $resultset = orm()->dummy->where('dummy')->notequals('foo')->and('dummy')->equals('testing');
        $count = count($resultset);
        $this->assertEqual($count, 1,
            "Expecting 1 result, got $count");
    } // }}}

    function test_delete_data_object() // {{{
    {
        $rs = orm()->dummy->where('dummy')->equals('testing');
        $this->assertEqual(count($rs), 1);

        $do =& $rs->first;
        $this->assertEqual($do->dummy, 'testing');

        $do->delete();
        $rs = orm()->dummy->where('dummy')->equals('testing');
        $this->assertEqual(count($rs), 0);
    } // }}}

    function test_data_object_field_validation() // {{{
    {
        orm()->register('quux')->int('id');
        $quux = orm()->quux;
        $do = $quux->create();
        $this->assertException('Minim_Orm_Exception',
            '$do->id = "foo!";');
    } // }}}

    function test_numeric_filters() // {{{
    {
        orm()->register_field_type('num', realpath(join(DIRECTORY_SEPARATOR,
            array(dirname(__FILE__), 'res', 'test_field_type.php'))), 'Num');

        $backend =& orm()->_backend;
        $backend->execute_query("CREATE TABLE num (value INTEGER)", array());

        $manager =& orm()->register('num')->num('value');
        $manager->create(array('value' => 1))->save();
        $manager->create(array('value' => 2))->save();
        $manager->create(array('value' => 3))->save();
        $manager->create(array('value' => 4))->save();

        $count = count($manager->where('value')->gt(1));
        $this->assertEqual($count, 3,
            "Expecting 3 results, got $count");

        $count = count($manager->where('value')->lt(2));
        $this->assertEqual($count, 1,
            "Expecting 1 result, got $count");

        $count = count($manager->where('value')->gte(3));
        $this->assertEqual($count, 2,
            "Expecting 2 results, got $count");

        $count = count($manager->where('value')->lte(4));
        $this->assertEqual($count, 4,
            "Expecting 4 results, got $count");

        $count = count($manager->where('value')->in_range(2, 4));
        $this->assertEqual($count, 3,
            "Expecting 3 results, got $count");
    } // }}}
} // }}}
