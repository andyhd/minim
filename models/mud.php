<?php
breve()->register_model('MudUser')
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
       ->field('last_update', breve()->text());

breve()->register_model('MudArea')
       ->table('mud_area')
       ->field('id', breve()->int(array('not_null' => TRUE,
                                        'autoincrement' => TRUE)))
       ->field('map', breve()->text(array('not_null' => TRUE)));

breve()->register_model('MudChat')
       ->table('mud_chat')
       ->field('id', breve()->int(array('not_null' => TRUE,
                                        'autoincrement' => TRUE)))
       ->field('user', breve()->int(array('not_null' => TRUE)))
       ->field('area', breve()->int(array('not_null' => TRUE)))
       ->field('msg', breve()->text(array('not_null' => TRUE)))
       ->field('at', breve()->text(array('not_null' => TRUE)));

breve()->register_model('MudUpdate')
       ->table('mud_update')
       ->field('id', breve()->int(array('not_null' => TRUE,
                                         'autoincrement' => TRUE)))
       ->field('at', breve()->text(array('not_null' => TRUE,
                                         'max_length' => 20)))
       ->field('type', breve()->int(array('not_null' => TRUE)))
       ->field('user', breve()->int(array('not_null' => TRUE)))
       ->field('area', breve()->int(array('not_null' => TRUE)))
       ->field('msg', breve()->text(array('not_null' => TRUE,
                                          'maxlength' => 255)));
