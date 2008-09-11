<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->models('{file}');

$model = breve('{model}');

minim()->render('admin/default/model-list', array(
    'model_name_plural' => '{model}s',
    'model_fields' => array_keys($model->_fields),
    'models' => $model->all()->items
));
