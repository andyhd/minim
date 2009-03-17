<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/forms/new_forms.php';

function forms()
{
    return minim('new_forms');
}

class Minim_Forms_TestCase extends TestCase // {{{
{
    function test_forms_construct_form() // {{
    {
        $form = forms()->create();
        $this->assertEqual('Minim_Form', get_class($form));
    } // }}}

    function test_forms_add_field() // {{{
    {
        $form = forms()->create();
        $this->assertEqual(0, count($form->_fields));
        $form->text('title');
        $this->assertEqual(1, count($form->_fields),
            "Form should have one text field, found nothing");
        $this->assertEqual('text', $form->_fields['title']->type,
            "Form field 'title' should have type 'text'");
        $this->assertEqual('text', $form->title->type,
            "Form field accessor for 'title' should have type 'text'");
    } // }}}

    function test_forms_render() // {{{
    {
        $form = forms()->create();
        $form->text('title');
        $out = $form->render();
        $this->assertTrue(strstr($out, '<form method='),
            "Form tag not found in form render output");
        $this->assertTrue(strstr($out, '<input type="text" name="title"'),
            "Form field 'title' not found in form render output");
    } // }}}

    function test_forms_submit() // {{{
    {
        $form = forms()->create();
        $form->text('title');
        $GLOBALS['_POST'] = array(
            'title' => 'foo'
        );
        $this->assertTrue($form->was_submitted(),
            "Form data not found in request");
        $this->assertEqual('foo', $form->title->value,
            "Submitted form field 'title' value mismatch: ".
            $form->title->value." != 'foo'");
        error_log(print_r($form, TRUE));
    } // }}}

    function test_forms_validation() // {{{
    {
        $form = forms()->create();
        $form->text('title', array(
            'validate' => create_function(
                '$field',
                'return $field->value == "foo";'
            )
        ));
        $GLOBALS['_POST'] = array(
            'title' => 'foo'
        );
        $form->was_submitted();
        $this->assertTrue($form->is_valid(),
            "Validation failed unexpectedly");
        $GLOBALS['_POST'] = array(
            'title' => 'bar'
        );
        $form->was_submitted();
        $this->assertTrue(!$form->is_valid(),
            "Validation passed unexpectedly");
    } // }}}

    function test_forms_from_model() // {{{
    {
        $manager = minim('orm')->register('dummy');
        $manager->int('foo')
                ->text('bar');
        $this->assertEqual(2, count($manager->_fields),
            "Manager should have 2 fields");
        $form = forms()->from_model('dummy');
        $this->assertEqual(2, count($form->_fields),
            "Form should have 2 fields");
    } // }}}
} // }}}
