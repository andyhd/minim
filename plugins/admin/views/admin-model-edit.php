<?php
$model_name = $_REQUEST['model'];

$action = @$_REQUEST['action'];
if ($action == 'new')
{
    $params = array();
}
else
{
    $model = minim('orm')->{$model_name};
    $id = $_REQUEST['id'];
    if ($model)
    {
        $model = $model->get(array('id' => $id));
    }
    if (!$model)
    {
        minim('templates')->render_404();
        return;
    }

    if ($action == 'delete')
    {
        $model->delete();
        minim('user_messaging')->info("Deleted $model_name #$id");
        minim('routing')->redirect('admin/model-list', array());
    }

    $params = array('instance' => $model);
}

$form = minim('forms')->form($model_name, $params);

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    $data = $_POST;
    if ($action == 'new')
    {
        unset($data['id']);
    }
    $form->from($data);
    if ($form->isValid())
    {
        $model = minim('orm')->{$model_name}->from($form->getData());
        $model->save();
        
        minim('user_messaging')->info("$model_name saved");
        minim('routing')->redirect('admin/model-list', $_GET);
    }
    else
    {
        $errors = $form->errors();
        minim('user_messaging')->info("Errors in form");
    }
}

minim('templates')->render('model-edit', array(
    'model_name' => $model_name,
    'form' => $form,
    'errors' => $errors
));
