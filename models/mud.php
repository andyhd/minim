<?php
breve()->register('MudUser')
       ->table('mud_user')
       ->field('id', breve()->int(array('not_null' => TRUE,
                                        'autoincrement' => TRUE)))
       ->field('user', breve()->int(array('not_null' => TRUE)))
       ->field('location', breve()->int(array('not_null' => TRUE)))
       ->field('x', breve()->int(array('not_null' => TRUE)))
       ->field('y', breve()->int(array('not_null' => TRUE)))
       ->field('state', breve()->int(array('not_null' => TRUE)))
       ->field('sprite', breve()->text(array('not_null' => TRUE,
                                             'maxlength' => 255)))
       ->field('last_update', breve()->timestamp(array('auto_now' => TRUE)));

breve()->register('MudArea')
       ->table('mud_area')
       ->field('id', breve()->int(array('not_null' => TRUE,
                                        'autoincrement' => TRUE)))
       ->field('map', breve()->text(array('not_null' => TRUE)));
