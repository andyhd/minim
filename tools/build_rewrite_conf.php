<?php
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
if ($base)
{
    fputs($fp, "RewriteBase $base\n");
}
$configfile = realpath(dirname(__FILE__).'/../config.php');
include $configfile;
foreach ($config['url_map'] as $name => $map)
{
    $pat = $map['url_pattern'];
    if ($base)
    {
        $pat = preg_replace(',^\^/,', '^', $pat);
    }
    $action = '';
    if (preg_match(',^(.+):(.+)$,', $name, $m))
    {
        $name = $m[1];
        $action = $m[2];
    }
    fputs($fp, "RewriteRule {$pat} views/{$name}.php");
    $params = array();
    if (preg_match_all(',\(\?P<(.*?)>.*?\),', $pat, $m))
    {
        foreach ($m[1] as $i => $param)
        {
            // mod_rewrite doesn't do named params :(
            $params[] = "$param=$". ($i + 1);
        }
        fputs($fp, "?".join('&', $params));
    }
    if ($action)
    {
        $prefix = $params ? '&' : '?';
        fputs($fp, "{$prefix}action=$action");
    }
    fputs($fp, " [QSA,L]\n");
}
