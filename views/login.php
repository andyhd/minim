<?php
require_once '../config.php';

$form = minim('forms')->form(array('id' => 'login-form'))
                      ->hiddenField('next', array(
                          'initial' => @$_REQUEST['continue']))
                      ->textField('email', array('label' => 'Email address'))
                      ->passwordField('password');
minim('log')->debug(print_r($form, TRUE));

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $user = minim('orm')->User->filter(array(
            'email__eq' => $form->email->getValue(),
            'password_hash__eq' => md5($form->password->getValue())
        ))->first;
        if ($user)
        {
            $_SESSION['user'] = array(
                'id' => $user->id,
                'name' => $user->name
            );
            $next = $form->next->getValue();
            if (!$next)
            {
                $next = 'home';
            }

            minim('routing')->redirect($next);
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

minim('templates')->render('login', array(
    'email' => @$_POST['email'],
    'form' => $form,
    'errors' => $errors
));