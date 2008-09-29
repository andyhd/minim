<?php
require_once '../../config.php';

$model_name = @$_REQUEST['model'];

if ($_REQUEST['action'] == 'new')
{
    $params = array();
}
else
{
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

    $params = array('instance' => $model);
}

$form = minim('forms')->form($model_name, $params);

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    minim('log')->debug('POST: '.print_r($_POST, TRUE));
    $form->from($_POST);
    if ($form->isValid())
    {
        minim('log')->debug('Form is valid, saving model');
        $model = minim('orm')->{$model_name}->from($form->getData());
        $model->save();
        
        minim('user_messaging')->info("$model_name saved");
        minim('routing')->redirect('admin/model-list', $_GET);
    }
    else
    {
        minim('log')->debug('Form is invalid');
        $errors = $form->errors();
        minim('user_messaging')->info("Errors in form");
    }
}

minim('templates')->render('admin/default/model-edit', array(
    'model_name' => $model_name,
    'form' => $form,
    'errors' => $errors
));
