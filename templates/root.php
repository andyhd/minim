<?php header('Content-type: text/html; charset=utf-8') ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
            "http://www.w3.org/TR/html4/strict.dtd">
<html>
 <head>
  <title><?php minim('templates')->block('title') ?></title>
<?php minim('templates')->block('meta') ?>
<?php minim('templates')->block('css') ?>
<?php minim('templates')->block('js_head') ?>
 </head>
 <body class="<?php minim('templates')->block('body_class') ?>">
<?php minim('templates')->block('content') ?>
<?php minim('templates')->block('js_foot') ?>
 </body>
</html>
