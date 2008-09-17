<?php
require_once '../../config.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');

$models = breve()->models_available();

minim('templates')->render('admin/default', array(
    'models' => $models,
));
