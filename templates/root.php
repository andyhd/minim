<?php header('Content-type: text/html; charset=utf-8') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title><?php $this->block('title') ?></title>
<?php $this->block('meta') ?>
<?php $this->block('css') ?>
<?php $this->block('js_head') ?>
 </head>
 <body class="<?php $this->block('body_class') ?>">
<?php $this->block('content') ?>
<?php $this->block('js_foot') ?>
 </body>
</html>
