<?php
error_reporting(E_STRICT|E_ALL);

require_once 'minim/minim.php';

// run all tests
minim('tests')->run_tests(realpath(join(DIRECTORY_SEPARATOR, array(
    dirname(__FILE__), '..'
))));

minim('tests')->output_results();
