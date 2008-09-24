#!/usr/bin/php
<?php
require realpath(dirname(__FILE__)."/../config.php");

if (!isset($argc) or $argc < 2)
{
    print "Missing argument(s)\n";
    exit;
}

$file = $argv[1];
if (is_file($file))
{
    print "$file already exists, overwrite? [yN] ";
    $response = fscanf(STDIN, '%s');
    if (strtolower($response[0]) !== 'y')
    {
        print "Aborting\n";
        exit;
    }
}

$base = '';
if (isset($argv[2]))
{
    $base = $argv[2];
}

$fp = fopen($file, 'w');
fputs($fp, "RewriteEngine On\n");

$rules = minim('routing')->mod_rewrite_rules($base);
fputs($fp, join("\n", $rules));

fclose($fp);
