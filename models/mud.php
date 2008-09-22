<?php
minim('orm')->register_model('MudUser')
            ->table('mud_user')
            ->int('id', array('not_null' => TRUE,
                              'autoincrement' => TRUE))
            ->int('user', array('not_null' => TRUE))
            ->int('location', array('not_null' => TRUE))
            ->int('x', array('not_null' => TRUE))
            ->int('y', array('not_null' => TRUE))
            ->int('state', array('not_null' => TRUE))
            ->text('sprite', array('not_null' => TRUE,
                                   'maxlength' => 255))
            ->text('last_update');

minim('orm')->register_model('MudArea')
            ->table('mud_area')
            ->int('id', array('not_null' => TRUE,
                              'autoincrement' => TRUE))
            ->text('map', array('not_null' => TRUE));

minim('orm')->register_model('MudChat')
            ->table('mud_chat')
            ->int('id', array('not_null' => TRUE,
                              'autoincrement' => TRUE))
            ->int('user', array('not_null' => TRUE))
            ->int('area', array('not_null' => TRUE))
            ->text('msg', array('not_null' => TRUE))
            ->text('at', array('not_null' => TRUE));

minim('orm')->register_model('MudUpdate')
            ->table('mud_update')
            ->int('id', array('not_null' => TRUE,
                              'autoincrement' => TRUE))
            ->text('at', array('not_null' => TRUE,
                               'max_length' => 20))
            ->int('type', array('not_null' => TRUE))
            ->int('user', array('not_null' => TRUE))
            ->int('area', array('not_null' => TRUE))
            ->text('msg', array('not_null' => TRUE,
                                'maxlength' => 255));
