<?php
require 'minim/plugins/tests/tests.php';
require 'minim/plugins/orm/orm.php';

function &orm() // {{{
{
    static $instance;
    if (!$instance)
    {
        $instance = new Minim_Orm();
    }
    return $instance;
} // }}}

class Minim_Orm_MySQL_TestCase extends TestCase // {{{
{
    function set_up() // {{{
    {
        `mysqladmin -u root -f create test_minim`;
    } // }}}

    function tear_down() // {{{
    {
        `mysqladmin -u root -f drop test_minim`;
    } // }}}

    function test_mysql_backend() // {{{
    {
        orm()->model_paths[] = realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        )));
        orm()->register('dummy');
        $manager = orm()->dummy;
        $this->assertEqual(count(orm()->_managers), 1);

        orm()->register_field_type('dummy', realpath(join(DIRECTORY_SEPARATOR,
            array(dirname(__FILE__), 'res', 'test_field_type.php'
        ))), 'Dummy');
        $manager->dummy('dummy');

        orm()->set_backend('mysql', array(
            'dbname' => 'test_minim',
            'user' => 'root',
            'pass' => '',
            'unix_socket' => '/tmp/mysql.sock'
        ));
        $backend =& orm()->_backend;
        $backend->execute_query("CREATE TABLE dummy (dummy TEXT)", array());
        $do = orm()->dummy->create();
        $value = md5(microtime());
        $do->dummy = $value;
        $do->save();
        $sth = $backend->execute_query('SELECT * FROM dummy', array());
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        $this->assertEqual($result['dummy'], $value);
    } // }}}
} // }}}

$test = new Minim_Orm_MySQL_TestCase();
dump_results($test->run());
