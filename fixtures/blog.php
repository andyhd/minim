<?php
$test_data = array(
    'post'     => array(
        'title'   => 'Lorem ipsum',
        'content' => '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi lacinia, mi vel laoreet tincidunt, quam quam viverra nunc, at blandit nisi mauris a lectus. Cras pharetra nunc id arcu. Praesent fermentum sapien eget eros. Vivamus nunc. Donec neque neque, mattis vitae, imperdiet at, varius aliquam, est. Suspendisse condimentum porta magna. In ullamcorper luctus purus. Nam porttitor, turpis vel auctor dictum, magna mi dapibus odio, et pellentesque massa lorem in est. Proin vitae risus. Suspendisse ornare nisl at erat. Nullam non justo. Nullam vehicula. Nam elit justo, hendrerit eget, fringilla eu, eleifend vel, tortor. Ut porttitor tincidunt risus. Aliquam aliquam vestibulum risus. Lorem ipsum dolor sit amet, consectetuer adipiscing elit. In placerat scelerisque nisi.</p><p>Quisque cursus cursus velit. Sed diam dolor, euismod ut, auctor et, ultrices sit amet, purus. Proin volutpat viverra mi. Fusce quis ipsum eget turpis dignissim rutrum. Mauris nunc. Cras eu lectus. Proin ac purus.</p>',
        'tags'    => array(
            'lorem', 'ipsum', 'foo'
        )
    ),
    'comments' => array(
        array(
            'author' => 'Andy',
            'posted' => strtotime('10 minutes ago'),
            'text'   => '<p>Quisque cursus cursus velit. Sed diam dolor, euismod ut, auctor et, ultrices sit amet, purus. Proin volutpat viverra mi. Fusce quis ipsum eget turpis dignissim rutrum. Mauris nunc. Cras eu lectus. Proin ac purus.</p>'
        ),
        array(
            'author' => 'Foobar',
            'posted' => strtotime('23 minutes ago'),
            'text'   => '<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi lacinia, mi vel laoreet tincidunt, quam quam viverra nunc, at blandit nisi mauris a lectus. Cras pharetra nunc id arcu. Praesent fermentum sapien eget eros. Vivamus nunc. Donec neque neque, mattis vitae, imperdiet at, varius aliquam, est. Suspendisse condimentum porta magna. In ullamcorper luctus purus. Nam porttitor, turpis vel auctor dictum, magna mi dapibus odio, et pellentesque massa lorem in est.</p>'
        )
    )
);
