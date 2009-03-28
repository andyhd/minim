<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/minim.php';

class Minim_TestCase extends TestCase
{
    function test_minim_get_plugin()
    {
        $minim = new Minim();
        $this->assertTrue($minim !== minim());

        $minim->plugin_paths = array(
            path(dirname(__FILE__), 'res')
        );

        $plugin =& $minim->get_plugin('dummy');
        $class = get_class($plugin);
        $this->assertTrue(($class == 'DummyPlugin'),
            "Class mismatch: $class");
    }

    function test_minim_get_unregistered_plugin()
    {
        $minim = new Minim();
        try
        {
            $minim->get_plugin('not_registered');
        }
        catch (Minim_Exception $me)
        {
            // test passes
            return;
        }
        catch (Exception $e)
        {
            $this->fail('Caught unexpected exception: '.$e->getMessage());
        }
        $this->fail('Expected Minim_Exception');
    }

    function test_minim_get_null_plugin()
    {
        $minim = new Minim();
        try
        {
            $minim->get_plugin(NULL);
        }
        catch (Minim_Exception $me)
        {
            // test passes
            return;
        }
        catch (Exception $e)
        {
            $this->fail('Caught unexpected exception: '.$e);
        }
        $this->fail('Expected Minim_Exception');
    }
}
