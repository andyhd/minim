<?php
class TestTest extends TestCase
{
    function test_test_pass()
    {
        $this->assertEqual(1, 1);
    }

    function test_test_fail()
    {
        $this->assertEqual(1, 2);
    }

    function test_test_error()
    {
        throw new Exception('Test error');
    }
}
