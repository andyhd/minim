<?php
class Minim_Admin implements Minim_Plugin
{
    var $root;

    function Minim_Admin() // {{{
    {
        $this->root = realpath(dirname(__FILE__));
    } // }}} 

    function enable() // {{{
    {
        $path = "{$this->root}/views";
        // set up admin urls
        minim('routing')
            ->map_url('^/admin$', 'default', NULL, $path)
            ->map_url('^/admin/models$', 'models', NULL, $path)
            ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)/new$',
                      'model-edit', 'new', $path)
            ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)$',
                      'model-edit', NULL, $path)
            ->map_url('^/admin/models/(?P<model>[a-zA-Z]+)$',
                      'model-list', NULL, $path)
            ->map_url('^/admin/routing$', 'routing', NULL, $path);
    } // }}}
}
