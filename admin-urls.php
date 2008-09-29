<?php
minim('routing')
    ->map_url('^/admin/models$', 'admin/models')
    ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)/(?P<id>(?:\d+|new))$',
              'admin/model-edit')
    ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)$', 'admin/model-list');
