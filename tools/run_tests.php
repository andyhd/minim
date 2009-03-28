<?php
error_reporting(E_STRICT|E_ALL);

require_once 'minim/minim.php';

// run all tests
minim('tests')->run_tests(path(dirname(__FILE__), '..'));
minim('tests')->output_results();
