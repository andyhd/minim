<?php
require_once '../../lib/minim.php';
require_once minim()->lib('breve-refactor');
require_once minim()->lib('defer');

$models = breve()->models_available();

minim()->render('admin/default', array(
    'models' => $models,
));
