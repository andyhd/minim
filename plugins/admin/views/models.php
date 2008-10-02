<?php
require_once '../../../config.php';

$models = minim('orm')->_available_models();

minim('templates')->render('models', array(
    'models' => $models,
), minim('admin')->root);
