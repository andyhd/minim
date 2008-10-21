<?php
$root = rtrim(dirname(dirname(realpath(__FILE__))), '/');
require_once "$root/config.php";

minim('tests');

// find all tests
$test_cases = array();
$dh = opendir("$root/tests");
while ($file = readdir($dh))
{
    if (substr($file, -4) == '.php')
    {
        // check for subclasses of TestCase
        if (preg_match('/class (\w*) extends TestCase\b/m',
                       file_get_contents("$root/tests/$file"),
                       $groups))
        {
            include "$root/tests/$file";
            $test_cases[] = $groups[1];
        }
    }
}

$out = '';
$hr = "\n".str_repeat('-', 80);

foreach ($test_cases as $test_class)
{
    $testcase = new $test_class();
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
