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

$map = "<?php\n";
foreach ($model_files as $file => $models)
{
    foreach ($models as $name)
    {
        minim()->log("Building admin views for $");
        
        $list_view = file_get_contents("$root/views/admin/model-list.tpl.php");
        $list_view = str_replace('{model}', $name, $list_view);
        $list_view = str_replace('{file}', $file, $list_view);
        file_put_contents("$root/views/admin/$name-list.php", $list_view);

        $edit_view = file_get_contents("$root/views/admin/model-edit.tpl.php");
        $edit_view = str_replace('{model}', $name, $edit_view);
        $edit_view = str_replace('{file}', $file, $edit_view);
        file_put_contents("$root/views/admin/$name-edit.php", $edit_view);

        // add to url map
        $map .= <<<PHP
\$this->map_url('^/admin/models/$name$', 'admin/$name-list')
      ->map_url('^/admin/models/$name/(?P<id>\d+)$', 'admin/$name-edit');

PHP;
    }
}

file_put_contents("$root/admin-urls.php", $map);

foreach (minim()->log_msgs as $msg)
{
    echo $msg, "\n";
}
