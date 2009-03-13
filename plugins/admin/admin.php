<?php
class Minim_Admin implements Minim_Plugin
{
    function enable()
    {
        minim('routing')->view_paths[] = build_path(
            dirname(__FILE__), 'views'
        );
        minim('templates')->template_paths[] = build_path(
            dirname(__FILE__), 'templates'
        );

        // point to routing and pagination helpers
        minim('templates')->helper_paths[] = build_path(
            minim('routing')->plugin_path, 'helpers.php'
        );
        minim('templates')->helper_paths[] = build_path(
            minim('pagination')->plugin_path, 'helpers.php'
        );

        // set up admin urls
        minim('routing')
            ->url('^admin$')
                ->maps_to('admin-default')
            ->url('^admin/models$')
                ->maps_to('admin-models')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<action>new)$')
                ->maps_to('admin-model-edit')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)$')
                ->maps_to('admin-model-edit')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)/(?P<action>delete)$')
                ->maps_to('admin-model-edit')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)$')
                ->maps_to('admin-model-list')
            ->url('^admin/login$')
                ->maps_to('admin-login');
    }
}
