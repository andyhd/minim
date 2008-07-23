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
    fputs($fp, "RewriteRule {$pat} controllers/{$name}.php");
    if (preg_match_all(',\(\?P<(.*?)>.*?\),', $pat, $m))
    {
        $params = array();
        foreach ($m[1] as $i => $param)
        {
            $params[] = "$param=$". ($i + 1);
        }
        fputs($fp, "?".join('&', $params));
    }
    fputs($fp, " [QSA,L]\n");
}
