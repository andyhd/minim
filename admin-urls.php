<?php
minim('routing')
    ->map_url('^/admin/models$', 'admin/models')
    ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)/new$', 'admin/model-edit', 'new')
    ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)$',
              'admin/model-edit')
    ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)$', 'admin/model-list');
