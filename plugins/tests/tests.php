<?php
class Minim_Testing implements Minim_Plugin
{
    var $_results;
    var $_time;
    var $_logfile;

    function Minim_Testing()
    {
        $this->_results = array();
    }

    /**
     * Run all tests found on the specified path. If no path specified, run
     * tests in the current directory.
     * @param string $path
     */
    function run_tests($path=NULL)
    {
        if (is_null($path))
        {
            $path = getcwd();
        }

        // capture error_log
        $this->_logfile = '/tmp/minim_test_log_'.date('Ymd');
        ini_set('error_log', $this->_logfile);
        error_log("Test run started ".str_repeat('-', 40));

        // find all tests
        $dir = new RecursiveDirectoryIterator($path);
        $test_cases = $this->get_test_cases($dir);

        if (count($test_cases) < 1)
        {
            print "No tests found";
            return;
        }

        $start_time = array_sum(explode(' ', microtime()));

        foreach ($test_cases as $case)
        {
            include $case['file'];
            $testcase = new $case['class']();
            $this->_results = array_merge($this->_results, $testcase->run());
        }

        $this->_time = array_sum(explode(' ', microtime())) - $start_time;
    }

    function output_results($use_colors=TRUE)
    {
        $out = '';
        $hr = str_repeat('-', 80);
        $green = $red = $reset = '';
        if ($use_colors)
        {
            $green = chr(27)."[1;32m";
            $red = chr(27)."[1;31m";
            $reset = chr(27)."[0m";
        }

        print "\n";

        $failures = 0;
        foreach ($this->_results as $test => $result)
        {
            switch ($result['result'])
            {
                case TestCase::PASS:
                    printf("  %sPASS%s  %-70s\n", $green, $reset, $test);
                    break;
                case TestCase::FAIL:
                    printf("  %sFAIL%s  %-70s\n", $red, $reset, $test);
                    printf("\n    %s\n\n", $result['reason']);
                    $failures++;
                    break;
                case TestCase::ERROR:
                    printf("  %sERROR%s %-70s\n", $red, $reset, $test);
                    $errmsg = "{$result['exception']}";
                    $errmsg = str_replace("\n", "\n    ", $errmsg);
                    printf("\n    %s\n", $result['reason']);
                    printf("\n    %s\n\n", $errmsg);
                    $failures++;
                    break;
            }
        }

        $num = count($this->_results);
        $time = sprintf('%.4f', $this->_time);
        print "\n$out$hr\nRan $num tests in $time seconds\n";
        if ($failures)
        {
            $plural = ($failures > 1) ? 's' : '';
            printf("%s%d test%s failed%s\n", $red, $failures, $plural, $reset);
        }
        print "\n\n";
    }


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
                        $this->get_test_cases($dir->getChildren()));
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
    } // }}}

    function assertOutputContains($a, $msg='') // {{{
    {
        $output = ob_get_contents();
        $this->assertTrue(strpos($output, $a) >= 0, $msg.($msg?"\n":'')."Output: $output");
    } // }}}

    function assertOutputMatches($a, $msg=NULL) // {{{
    {
        $this->assertTrue(preg_match($a, ob_get_contents()), $msg);
    } // }}}

    function run() // {{{
    {
        // find methods starting with 'test_'
        $methods = get_class_methods(get_class($this));
        $methods = array_filter($methods, array(&$this, 'is_test'));

        // execute each one
        $results = array_combine($methods, array_fill(0, count($methods), array(
            'result' => NULL,
            'reason' => ''
        )));
        foreach ($methods as $test)
        {
            $this->set_up();
            ob_start();
            try
            {
                $result = $this->$test();
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
            ob_end_clean();
            $this->tear_down();
            if ($results[$test]['result'] === NULL)
            {
                $results[$test]['result'] = TestCase::PASS;
                if (is_array($result))
                {
                    $results[$test] = $result;
                }
            }
        }
        return $results;
    } // }}}
} // }}}
