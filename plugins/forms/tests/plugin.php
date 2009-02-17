<?php
require_once 'minim/plugins/tests/tests.php';
require_once 'minim/plugins/forms/new_forms.php';

function forms()
{
    return minim('new_forms');
}

class Minim_Forms_TestCase extends TestCase // {{{
{
    function test_construct_form() // {{
    {
        $form = forms()->create();
        $this->assertEqual('Minim_Form', get_class($form));
    } // }}}

    function test_add_form_field() // {{{
    {
        $form = forms()->create();
        $this->assertEqual(0, count($form->_fields));
        $form->text('title');
        $this->assertEqual(1, count($form->_fields),
            "Form should have one text field, found nothing");
        $this->assertEqual('text', $form->_fields['title']['type'],
            "Form field 'title' should have type 'text'");
        $this->assertEqual('text', $form->title['type'],
            "Form field accessor for 'title' should have type 'text'");
    } // }}}
} // }}}
