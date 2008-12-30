<?php
require_once 'minim/minim.php';

// run all tests
minim('tests')->run_tests(realpath(join(DIRECTORY_SEPARATOR, array(
    dirname(__FILE__), '..'
))));

minim('tests')->output_results();
