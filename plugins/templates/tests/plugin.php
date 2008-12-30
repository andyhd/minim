<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/templates/plugin.php';

function &tmpl() // {{{
{
    static $tmpl;
    if (!$tmpl)
    {
        $tmpl = new Minim_TemplateEngine();
    }
    return $tmpl;
} // }}}

class Minim_TemplateEngine_TestCase extends TestCase
{
    function test_tmpl() // {{{
    {
        $tmpl =& tmpl();
        $this->assertTrue($tmpl === tmpl());
    } // }}}

    function test_simple_template() // {{{
    {
        $this->assertEqual(count(tmpl()->_template_paths), 0);

        tmpl()->add_template_path(realpath(join(DIRECTORY_SEPARATOR, array(
            dirname(__FILE__), 'res'
        ))));
        $this->assertEqual(count(tmpl()->_template_paths), 1);

        tmpl()->render('simple-template', array('world' => 'world!'));
        $this->assertOutput('hello world!');
    } // }}}

    function test_setting_block() // {{{
    {
        $this->assertEqual(count(tmpl()->_blocks), 0);

        tmpl()->render('child');
        $this->assertEqual(count(tmpl()->_blocks), 1);

        $this->assertEqual(tmpl()->_blocks['child'], 'child');
    } // }}}

    function test_getting_block() // {{{
    {
        $this->assertEqual(count(tmpl()->_blocks), 1);

        tmpl()->render('parent');
        $this->assertOutput('parent contains child');
    } // }}}

    function test_extend_template() // {{{
    {
        tmpl()->render('extender');
        $this->assertOutput("extender extends base");
    } // }}}
}
