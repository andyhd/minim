<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('pagination');

$model_name = @$_REQUEST['model'];

$model = breve($model_name);
if ($model == NULL)
{
    minim()->render_404();
}

$models = $model->all();
if ($model->default_sort())
{
    $models->order_by($model->default_sort());
}

$paginator = new BrevePaginator($models, 'admin/model-list', array(
    'model' => $model_name
));

minim()->render('admin/default/model-list', array(
    'model_name' => $model_name,
    'model_name_plural' => "{$model_name}s",
    'model_fields' => array_keys($model->_fields),
    'models' => $paginator
));
