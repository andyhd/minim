<?php header('Content-type: text/html; charset=utf-8') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title><?php $this->get('title') ?></title>
<?php $this->get('meta') ?>
<?php $this->get('css') ?>
<?php $this->get('js_head') ?>
 </head>
 <body class="<?php $this->get('body_class') ?>">
<?php $this->get('content') ?>
<?php $this->get('js_foot') ?>
 </body>
</html>
