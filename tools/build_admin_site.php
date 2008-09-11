#!/usr/bin/php
<?php
// build admin site for models
$root = realpath(dirname(__FILE__).'/../');
require_once "$root/lib/minim.php";
require_once minim()->lib('breve-refactor');

if ($argc and in_array('--verbose', $argv))
{
    minim()->debug = TRUE;
}

// create view and template directories if they don't exist
/*mkdir("$root/views/admin");
mkdir("$root/templates/admin/default");
mkdir("$root/css/admin/default");
mkdir("$root/js/admin/default");
*/
// get all models
$models = array();
$dh = opendir(minim()->root."/models");
if (!$dh)
{
    die('Could not open '.minim()->root.'/models directory');
}
$model_files = array();
while ($dl = readdir($dh))
{
    if (substr($dl, strlen($dl)-4) == '.php')
    {
        $file = minim()->root."/models/$dl";
        $models_registered = array_keys(breve()->_managers);
        require_once $file;
        $new_models = array();
        foreach (array_keys(breve()->_managers) as $model)
        {
            if (!in_array($model, $models_registered))
            {
                $new_models[] = $model;
            }
        }
        $model_files[substr($dl, 0, -4)] = $new_models;
    }
}

minim()->log("Loaded models:".print_r($model_files, TRUE));

// add to url map
$map .= <<<HTML
<?php
\$this->map_url('^/admin/models/(?<model>[a-z]+)$', 'admin/model-list')
     ->map_url('^/admin/models/(?<model>[a-z]+)/(?P<id>\d+)$', 'admin/model-edit');
?>

HTML;
    }
}

file_put_contents("$root/admin-urls.php", $map);

foreach (minim()->log_msgs as $msg)
{
    echo $msg, "\n";
}
