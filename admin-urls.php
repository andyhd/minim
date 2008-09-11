<?php
$this->map_url('^/admin/models/(?P<model>[a-zA-Z]+)$', 'admin/model-list')
     ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)$', 'admin/model-edit');
