<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('quaver');
require_once minim()->models('user');

$form = minim()->form(array('id' => 'login-form'))
               ->hiddenField('next', array('initial' => @$_REQUEST['continue']))
               ->textField('name', array('label' => 'Username'))
               ->passwordField('password');
minim()->log(print_r($form, TRUE));

$errors = NULL;
if ($_SERVER['REQUEST_METHOD'] == 'post')
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $user = breve('User')->filter(array(
            'name' => $form->name->getValue(),
            'password_hash' => md5($form->password->getValue())
        ));
        if ($user)
        {
            $_SESSION['user'] = $user->id;
            $next = $form->continue->getValue();
            if (!$next)
            {
                $next = 'home';
            }

            minim()->redirect($next);
        }
        $errors = array(
            'Login failed'
        );
    }
    else
    {
        $errors = $form->errors();
    }
}

minim()->render('login', array(
    'username' => @$_POST['name'],
    'form' => $form,
    'errors' => $errors
));
