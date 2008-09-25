<?php
require_once '../../config.php';

$models = minim('orm')->_available_models();

if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    if ($model = $_REQUEST['create_table'])
    {
        minim('orm')->create_database_table($model);
        minim('user_messaging')->info("Created database table for $model");
        minim('routing')->redirect('admin/models');
    }
}

minim('templates')->render('admin/models', array(
    'models' => $models,
));
