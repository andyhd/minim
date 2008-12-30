<?php
require_once 'minim/plugins/tests/tests.php';

// find all tests
$dir = new RecursiveDirectoryIterator(realpath(join(DIRECTORY_SEPARATOR, array(
    dirname(__FILE__), '..'
))));

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
                        and preg_match($pattern, file_get_contents($file), $m))
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

$test_cases = get_test_cases($dir);

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
