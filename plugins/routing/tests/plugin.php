<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/routing/routing.php';

class RoutingTests extends TestCase
{
    function set_up()
    {
        $this->router = new Minim_Router();
        $this->router->view_paths[] = realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        )));
    }

    function tear_down()
    {
        $this->router = NULL;
    }

    function test_route_map_url_default()
    {
        $this->router->url('foo');
        $route = $this->router->resolve('foo');
        $this->assertEqual('foo', $route->url_pattern);
    }

    function test_route_map_url()
    {
        $view_path = $this->router->view_paths[0];
        $this->router->url('foo')->maps_to('bar');
        $route = $this->router->resolve('foo');
        $this->assertEqual("$view_path/bar.php", $route->view);
    }

    function test_route_url_for()
    {
        $this->router->url('foo')->maps_to('foo');
        $this->assertEqual(1, count($this->router->_routes));

        $url = $this->router->url_for('foo');
        $this->assertEqual('/foo', $url);
    }

    function test_route_url_missing()
    {
        $this->assertEqual(0, count($this->router->_routes));
        $this->assertException('Minim_Router_Exception',
            '$this->router->url_for("blarch");');
    }

    function test_route_url_with_parameters()
    {
        $this->router->url('foo/(?P<id>\d+)')->maps_to('foo');
        $route = $this->router->resolve('foo/42');
        $this->assertEqual('42', $route->params['id']);

        $url = $this->router->url_for('foo', array('id' => 99));
        $this->assertEqual('/foo/99', $url);

        $this->assertException('Minim_Router_Exception',
            '$this->router->url_for("foo");');
    }

    function test_route_request()
    {
        $this->router->url('foo/(?P<id>\d+)')->maps_to('foo');
        $GLOBALS['_SERVER'] = array(
            'REQUEST_URI' => 'http://localhost/foo/27'
        );
        $this->router->route_request();
        $this->assertOutput('foo27');
    }
}
