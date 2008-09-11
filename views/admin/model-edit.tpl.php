<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('quaver');
require_once minim()->models('{file}');

$model = breve('{model}')->filter(array(
    'id__eq' => $_GET['id']
))->first;

if (!$model)
{
    minim()->render_404();
    return;
}

$form = minim()->form('{model}', $model->to_array());

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD'] == 'post'))
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $model = breve('{model}')->from($form->getData());
        $model->save();
        
        minim()->user_message('{model} saved');
        minim()->redirect('admin/{model}-list', $_GET);
    }
    else
    {
        $errors = $form->errors();
    }
}

minim()->render('admin/default/{model}-edit', array(
    'model' => $model
));
