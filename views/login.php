<?php
require_once '../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');
require_once minim()->lib('quaver');

$form = minim()->form(array('id' => 'login-form'))
               ->hiddenField('next', array('initial' => @$_REQUEST['continue']))
               ->textField('email', array('label' => 'Email address'))
               ->passwordField('password');
minim()->log(print_r($form, TRUE));

$errors = NULL;
if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
{
    $form->from($_POST);
    if ($form->isValid())
    {
        $user = breve('User')->filter(array(
            'email__eq' => $form->email->getValue(),
            'password_hash__eq' => md5($form->password->getValue())
        ));
        if ($user->first)
        {
            $_SESSION['user'] = array(
                'id' => $user->first->id,
                'name' => $user->first->name
            );
            $next = $form->next->getValue();
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
    'email' => @$_POST['email'],
    'form' => $form,
    'errors' => $errors
));
