<?php
require_once '../../config.php';

$model_name = @$_REQUEST['model'];

$model = minim('orm')->{$model_name};
if ($model == NULL)
{
    minim('templates')->render_404();
}

$models = $model->all();
if ($model->default_sort())
{
    $models->order_by($model->default_sort());
}

$paginator = minim('pagination')->source($models)
                                ->base_url('admin/model-list', array(
                                    'model' => $model_name
                                ));

minim('templates')->render('admin/default/model-list', array(
    'model_name' => $model_name,
    'model_name_plural' => "{$model_name}s",
    'model_fields' => array_keys($model->_fields),
    'models' => $paginator
));
