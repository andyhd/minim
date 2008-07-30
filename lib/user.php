<?php
breve()->register('User')
       ->table('user')
       ->field('id', breve()->int(array('not_null' => TRUE,
                                        'autoincrement' => TRUE)))
       ->field('name', breve()->text(array('maxlength' => 100)))
       ->field('email', breve()->text(array('maxlength' => 255)));
