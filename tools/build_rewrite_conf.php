#!/usr/bin/php
<?php
$options = getopt('c:f:h');
if (@$options['h'])
{
    $syntax = <<<TEXT
Syntax:  {$argv[0]}
Options: -c <config file>
         -f <output file>

TEXT;
    die($syntax);
}

if (@$options['c'])
{
    $config = realpath($options['c']);
    if (!$config)
    {
        $config = realpath(join(DIRECTORY_SEPARATOR, array(
            getcwd(), $options['c']
        )));
    }
    if (is_file($config))
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

$file = realpath(@$options['f']);
if (!$file)
{
    $file = realpath(join(DIRECTORY_SEPARATOR, array(
        getcwd(), @$options['f']
    )));
}
$fh = STDOUT;
if (is_file($file))
{
    print "$file already exists, overwrite? [yN] ";
    $response = fscanf(STDIN, '%s');
    if (strtolower($response[0]) !== 'y')
    {
        print "Aborting\n";
        exit;
    }
    $fh = fopen($file, 'w');
}

$base = '';
if (isset(minim()->webroot))
{
    $base = minim()->webroot;
}

fputs($fh, "RewriteEngine On\n");

$rules = minim('routing')->mod_rewrite_rules($base);
fputs($fh, join("\n", $rules));
