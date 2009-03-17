<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/templates/templates.php';

function &tmpl()
{
    static $tmpl;
    if (!$tmpl)
    {
        $tmpl = new Minim_TemplateEngine();
    }
    return $tmpl;
}

class Minim_TemplateEngine_TestCase extends TestCase
{
    function test_tmpl_simple_template()
    {
        $this->assertEqual(count(tmpl()->template_paths), 0);

        tmpl()->template_paths[] = build_path(
            dirname(__FILE__), 'res'
        );
        $this->assertEqual(count(tmpl()->template_paths), 1);

        tmpl()->render('simple-template', array('world' => 'world!'));
        $this->assertOutput('hello world!');
    }

    function test_tmpl_set_block()
    {
        $this->assertEqual(count(tmpl()->_blocks), 0);

        tmpl()->render('child');
        $this->assertEqual(count(tmpl()->_blocks), 1);

        $this->assertEqual(tmpl()->_blocks['child'], 'child');
    }

    function test_tmpl_get_block()
    {
        $this->assertEqual(count(tmpl()->_blocks), 1);

        tmpl()->render('parent');
        $this->assertOutput('parent contains child');
    }

    function test_tmpl_extend_template()
    {
        tmpl()->render('extender');
        $this->assertOutput("extender extends base");
    }

    function test_tmpl_load_helper()
    {
        $tmpl = new Minim_TemplateEngine();
        $this->assertEqual(0, count($tmpl->helper_paths));
        $tmpl->helper_paths[] = join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res', 'test_helper.php'
        ));
        $tmpl->load_helper('test_helper');
        $this->assertEqual(1, count($tmpl->_helpers),
            "Expected 1 helper loaded");
        $ok = $tmpl->test_helper();
        $this->assertEqual('test ok', $ok,
            "Test helper returned unexpected value $ok");
    }
}
