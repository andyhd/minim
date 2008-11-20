<?php
class TestFailure extends Exception {}

class TestCase implements Minim_Plugin // {{{
{
    const PASS = 0;
    const FAIL = 1;
    const ERROR = 2;

    function set_up() // {{{
    {
    } // }}}

    function tear_down() // {{{
    {
    } // }}}

    function is_test($method_name) // {{{
    {
        return strpos(strtolower($method_name), 'test_') === 0;
    } // }}}

    function fail($reason = NULL) // {{{
    {
        throw new TestFailure($reason);
    } // }}}

    function assertEqual($a, $b) // {{{
    {
        if ($a == $b)
        {
            return TRUE;
        }
        $this->fail("Assertion failure: $a != $b");
    } // }}}

    function run() // {{{
    {
        // find methods starting with 'test_'
        $methods = get_class_methods(get_class($this));
        $methods = array_filter($methods, array(&$this, 'is_test'));

        // execute each one
        $results = array_combine($methods, array_fill(0, count($methods), array(
            'result' => TestCase::PASS,
            'reason' => ''
        )));
        foreach ($methods as $test)
        {
            try
            {
                $this->set_up();
                $result = $this->$test();
                $this->tear_down();
                if (is_array($result))
                {
                    $results[$test] = $result;
                }
            }
            catch (TestFailure $f)
            {
                $results[$test] = array(
                    'result' => TestCase::FAIL,
                    'reason' => $f->getMessage()
                );
            }
            catch (Exception $e)
            {
                $results[$test] = array(
                    'result' => TestCase::ERROR,
                    'reason' => $e->getMessage(),
                    'exception' => $e
                );
            }
        }
        return $results;
    } // }}}
} // }}}