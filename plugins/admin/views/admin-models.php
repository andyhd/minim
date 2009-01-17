<?php
$models = array();
$pattern = '/->register\(([\'"])(.*?)\1/';
foreach (minim('orm')->model_paths as $path)
{
    $dir = new DirectoryIterator($path);
    foreach ($dir as $file)
    {
        $filename = $file->getPathname();
        if (substr($filename, -4) == '.php'
            and preg_match($pattern, file_get_contents($filename), $m))
        {
            $models[] = $m[2];
        }
    }
}

minim('templates')->render('models', array(
    'models' => $models,
));
