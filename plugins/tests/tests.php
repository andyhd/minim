<?php
class TestFailure extends Exception {}

function dump_results($results)
{
    $out = '';
    $hr = "\n".str_repeat('-', 80);

    foreach ($results as $test => $result)
    {
        switch ($result['result'])
        {
            case TestCase::PASS:
                print '.';
                break;
            case TestCase::FAIL:
                print 'F';
                $out .= <<<TEXT
$hr
FAIL: $test
    {$result['reason']}
TEXT;
                break;
            case TestCase::ERROR:
                print 'E';
                $errmsg = "{$result['exception']}";
                $errmsg = str_replace("\n", "\n    ", $errmsg);
                $out .= <<<TEXT
$hr
ERROR: $test
    {$result['reason']}

    $errmsg
TEXT;
                break;
        }
    }
    print "\n$out\n\n";
}

class TestCase // {{{
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

    function assertEqual($a, $b, $msg=NULL) // {{{
    {
        if ($a == $b)
        {
            return TRUE;
        }
        $this->fail($msg ? $msg : "Assertion failure: $a != $b");
    } // }}}

    function assertTrue($a, $msg=NULL) // {{{
    {
        $this->assertEqual($a, TRUE, $msg);
    } // }}}

    function assertException($exception_class, $code, $msg=NULL) // {{{
    {
        try
        {
            eval($code);
        }
        catch (Exception $e)
        {
            if (get_class($e) != $exception_class)
            {
                throw $e;
            }
            return TRUE;
        }
        $this->fail($msg ? $msg : "Expected $exception_class not thrown");
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
