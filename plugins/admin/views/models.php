<?php
$models = minim('orm')->_available_models();

minim('templates')->render('models', array(
    'models' => array_keys($models),
));
