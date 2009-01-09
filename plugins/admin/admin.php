<?php
class Minim_Admin implements Minim_Plugin
{
    var $root;

    function Minim_Admin() // {{{
    {
        $this->root = realpath(dirname(__FILE__));
        minim('templates')->add_template_path(join(DIRECTORY_SEPARATOR,
            array($this->root, "templates")
        ));
        minim('routing')->view_paths[] = join(DIRECTORY_SEPARATOR, array(
            $this->root,'views'
        ));
    } // }}} 

    function enable() // {{{
    {
        // set up admin urls
        minim('routing')
            ->url('^admin$')
                ->maps_to('admin-default')
            ->url('^admin/models$')
                ->maps_to('admin-models')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<action>new|delete)$')
                ->maps_to('admin-model-edit')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)$')
                ->maps_to('admin-model-edit')
            ->url('^admin/models/(?P<model>[a-zA-Z]+)$')
                ->maps_to('admin-model-list');
    } // }}}
}
