<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('quaver');

$model_name = @$_REQUEST['model'];

$model = breve($model_name);
if (!$model)
{
    minim()->render_404();
    return;
}

$model = $model->filter(array(
    'id__eq' => @$_REQUEST['id']
))->first;
if (!$model)
{
    minim()->render_404();
    return;
}

$form = minim()->form($model_name, $model->to_array());

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD'] == 'post'))
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $model = breve($model_name)->from($form->getData());
        $model->save();
        
        minim()->user_message("$model_name saved");
        minim()->redirect('admin/model-list', $_GET);
    }
    else
    {
        $errors = $form->errors();
    }
}

minim()->render('admin/default/model-edit', array(
    'model_name' => $model_name,
    'form' => $form,
    'errors' => $errors
));
