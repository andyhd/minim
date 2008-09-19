<?php
require_once '../../config.php';

$models = minim('orm')->_available_models();

minim('templates')->render('admin/default', array(
    'models' => $models,
));
