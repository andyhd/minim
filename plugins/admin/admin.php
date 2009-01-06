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
        error_log('Template paths: '.print_r(minim('templates')->template_paths, TRUE));
    } // }}} 

    function enable() // {{{
    {
        $path = "{$this->root}/views";
        // set up admin urls
        minim('routing')
            ->map_url('^admin$', 'admin/default', NULL, "$path/default.php")
            ->map_url('^admin/models$', 'admin/models', NULL,
                      "$path/models.php")
            ->map_url('^admin/models/(?P<model>[a-zA-Z]+)/new$',
                      'admin/model-edit', 'new', "$path/model-edit.php")
            ->map_url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)$',
                      'admin/model-edit', NULL, "$path/model-edit.php")
            ->map_url('^admin/models/(?P<model>[a-zA-Z]+)/(?P<id>\d+)/delete$',
                      'admin/model-edit', 'delete', "$path/model-edit.php")
            ->map_url('^admin/models/(?P<model>[a-zA-Z]+)$',
                      'admin/model-list', NULL, "$path/model-list.php");
    } // }}}
}
