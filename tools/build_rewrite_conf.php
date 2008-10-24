#!/usr/bin/php
<?php
$options = getopt('c::');
if (!isset($argc) or $argc < 1 or !$options)
{
    $syntax = <<<TEXT
Syntax:  {$argv[0]} <file>
Options: -c <config file>

TEXT;
    die($syntax);
}

if ($options['c'])
{
    $config = realpath($options['c']);
    if ($config and is_file($config))
    {
        require $config;
    }
    else
    {
        die("Config file {$options['c']} not found");
    }
}
else
{
    require 'config.php';
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
if (isset(minim()->webroot))
{
    $base = minim()->webroot;
}

$fp = fopen($file, 'w');
fputs($fp, "RewriteEngine On\n");

$rules = minim('routing')->mod_rewrite_rules($base);
fputs($fp, join("\n", $rules));

fclose($fp);
