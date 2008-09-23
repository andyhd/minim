<?php
require_once '../../config.php';

$models = minim('orm')->_available_models();

minim('templates')->render('admin/models', array(
    'models' => $models,
));
