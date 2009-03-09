<?php
$model_name = $_GET['model'];

$model = minim('orm')->{$model_name};
if ($model == NULL)
{
    error_log("Model $model_name not found");
    minim('templates')->render_404();
}

$models = $model->all();

function paginator(&$models, $model_name)
{
    return minim('pagination')->source($models)
                              ->base_url('admin/model-list', array(
                                    'model' => $model_name
                                ));
}

try
{
    $paginator = paginator($models, $model_name);
}
catch (Exception $e)
{
    switch ($e->getCode())
    {
        case '42S02': // table does not exist
            // create table automatically and try again
            minim('orm')->create_database_table($model_name);
            minim('user_messaging')->info("Created database table for $model_name");
            $paginator = paginator($models, $model_name);
            break;
        default:
            throw $e;
    }
}

minim('templates')->render('model-list', array(
    'model_name' => $model_name,
    'model_name_plural' => "{$model_name}s",
    'model_fields' => array_keys($model->_fields),
    'models' => $paginator
));
