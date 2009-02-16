<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/admin/admin.php';


class Minim_Admin_TestCase extends TestCase
{
    function test_admin_enable()
    {
        $admin = minim('admin');
        $templates = minim('templates');
        $routing = minim('routing');
        $this->assertEqual(0, count($templates->template_paths));
        $this->assertEqual(0, count($routing->view_paths));
        $admin->enable();
        $this->assertEqual(1, count($templates->template_paths));
        $this->assertEqual(build_path(dirname(__FILE__), '..', 'templates'),
            $templates->template_paths[0]);
        $this->assertEqual(1, count($routing->view_paths));
        $this->assertEqual(build_path(dirname(__FILE__), '..', 'views'),
            $routing->view_paths[0]);
    }

    function test_admin_models()
    {
        $admin = minim('admin');
        
        // register a test model
        $orm = minim('orm');
        $orm->model_paths[] = realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        )));
        $manager = $orm->test;
        $this->assertEqual(count($orm->_managers), 1);
        $this->assertEqual($manager->_model, 'test');
        $admin->enable();

        // request models view
        $GLOBALS['_SERVER'] = array(
            'REQUEST_URI' => 'http://localhost/admin/models'
        );
        minim('routing')->route_request();
        $this->assertOutputContains('/admin/models/test');
    }

    function test_admin_model_list()
    {
        $admin = minim('admin');

        // register a test model
        $orm = minim('orm');
        $orm->model_paths[] = realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        )));
        $model = 'test';
        $manager = $orm->$model;
        $this->assertEqual(count($orm->_managers), 1);
        $this->assertEqual($manager->_model, $model);
        $admin->enable();

        // request models view
        $GLOBALS['_SERVER'] = array(
            'REQUEST_URI' => "http://localhost/admin/model/$model"
        );
        minim('routing')->route_request();
        $this->assertOutputContains("/admin/model/$model/1");
    }

    function test_admin_model_edit()
    {
        minim('admin')->enable();

        $orm = minim('orm');
        $orm->set_backend('sqlite', array('database' => ':memory:'));
        $orm->_backend->execute_query('CREATE TABLE baz (id INT);', array());
        $orm->register('baz')->int('id');

        // save a test model
        $baz = minim('orm')->baz;
        $baz->create(array('id' => 1))->save();
        $this->assertEqual(1, $baz->get(array('id' => 1))->id);

        // request the model edit page
        $GLOBALS['_SERVER'] = array(
            'REQUEST_URI' => 'http://localhost/admin/models/baz/1'
        );
        minim('routing')->route_request();
        $this->assertOutputContains('flobadob');
    }
}
