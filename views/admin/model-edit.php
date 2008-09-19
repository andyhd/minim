<?php
require_once '../../config.php';

$model_name = @$_REQUEST['model'];

$model = minim('orm')->{$model_name};
if (!$model)
{
    minim('templates')->render_404();
    return;
}

$model = $model->filter(array(
    'id__eq' => @$_REQUEST['id']
))->first;
if (!$model)
{
    minim('templates')->render_404();
    return;
}

$form = minim('forms')->form($model_name, array('instance' => $model));

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD'] == 'post'))
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $model = minim('orm')->{$model_name}->from($form->getData());
        $model->save();
        
        minim('user_messaging')->info("$model_name saved");
        minim('templates')->redirect('admin/model-list', $_GET);
    }
    else
    {
        $errors = $form->errors();
    }
}

minim('templates')->render('admin/default/model-edit', array(
    'model_name' => $model_name,
    'form' => $form,
    'errors' => $errors
));
