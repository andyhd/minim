#!/usr/bin/php
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

class Foo
{
    function Foo($configfile)
    {
        $this->maps = array();
        include $configfile;
    }

    function map_url($pattern, $view, $action=null)
    {
        $this->maps[] = array(
            'pat' => $pattern,
            'view' => $view,
            'action' => $action
        );
        return $this;
    }
}

$foo = new Foo(realpath(dirname(__FILE__).'/../config.php'));
foreach ($foo->maps as $map)
{
    extract($map);
    if ($base)
    {
        $pat = preg_replace(',^\^/,', '^', $pat);
    }
    fputs($fp, "RewriteRule {$pat} views/{$view}.php");
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

// admin rule last allows previous more specific rules to override
fputs($fp, "RewriteRule ^".(!$base ? '/' : '')."admin(?:/(.*))?$ views/admin.php?path=$1 [QSA,L]\n");

fclose($fp);

