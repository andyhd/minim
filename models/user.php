<?php
minim('orm')->register_model('User')
            ->table('user')
            ->int('id', array('not_null' => TRUE,
                              'autoincrement' => TRUE))
            ->text('name', array('maxlength' => 100))
            ->text('email', array('maxlength' => 255))
            ->text('password_hash', array('maxlength' => 32));
