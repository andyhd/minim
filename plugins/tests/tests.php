<?php

class Minim_Testing
{
    function run_tests()
    {
        $path = getcwd();

        // find all tests
        $dir = new RecursiveDirectoryIterator($path);
        $test_cases = get_test_cases($dir);

        if (count($test_cases) < 1)
        {
            print "No tests found";
            return;
        }

$out = '';
$hr = "\n".str_repeat('-', 80);

foreach ($test_cases as $case)
{
    include $case['file'];
    $testcase = new $case['class']();
    $results = $testcase->run();

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
}
echo "\n$out\n\n";



    function get_test_cases($dir)
    {
        static $pattern = '/class (\w*) extends TestCase\b/m';
        $test_cases = array();
        foreach ($dir as $path)
        {
            if ($dir->hasChildren())
            {
                if (substr($path, -5) == 'tests')
                {
                    foreach ($dir->getChildren() as $file)
                    {
                        if (substr($file, -4) == '.php'
                            and preg_match($pattern, file_get_contents($file),
                                           $m))
                        {
                            $test_cases[] = array(
                                'file' => $file->getPathname(),
                                'class' => $m[1]
                            );
                        }
                    }
                }
                else
                {
                    $test_cases = array_merge($test_cases,
                        get_test_cases($dir->getChildren()));
                }
            }
        }
        return $test_cases;
    }
}

class TestFailure extends Exception {}

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

    function assertOutput($a, $msg=NULL) // {{{
    {
        $this->assertEqual($a, ob_get_contents(), $msg);
        ob_clean();
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
                ob_start();
                $result = $this->$test();
                ob_end_clean();
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
