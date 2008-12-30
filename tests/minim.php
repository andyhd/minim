<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/minim.php';

class Minim_TestCase extends TestCase // {{{
{
    function test_minim() // {{{
    {
        $minim = minim();
        $this->assertTrue($minim === minim());
    } // }}}

    function test_get_plugin() // {{{
    {
        $minim = new Minim();
        $this->assertTrue($minim !== minim());

        $this->assertEqual(0, count($minim->_plugins));

        $minim->_plugin_paths = array(realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        ))));
        $minim->_init_plugins();
        $this->assertEqual(1, count($minim->_plugins),
            "No plugins registered");

        $this->assertTrue(array_key_exists('dummy', $minim->_plugins),
            "Dummy plugin not registered");

        $plugin =& $minim->get_plugin('dummy');
        $class = get_class($plugin);
        $this->assertTrue(($class == 'DummyPlugin'),
            "Class mismatch: $class");
    } // }}}
} // }}}
