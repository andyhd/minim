<?php header('Content-type: text/html; charset=utf-8') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title><?php minim()->block('title') ?></title>
<?php minim()->block('meta') ?>
<?php minim()->block('css') ?>
<?php minim()->block('js_head') ?>
 </head>
 <body class="<?php minim()->block('body_class') ?>">
<?php minim()->block('content') ?>
<?php minim()->block('js_foot') ?>
 </body>
</html>
