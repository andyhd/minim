<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Game<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_css') ?>
  <link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/platformer.css">
<?php minim()->end_block('page_css') ?>

<?php minim()->def_block('page_js_head') ?>
  <script type="text/javascript" src="<?php echo minim()->webroot ?>/js/platformer.js"></script> 
  <script type="text/javascript">
jQuery(function () {
    init();
    jQuery(window).keydown(function (e) {
        return handleKeyDown(e.originalEvent);
    }).keyup(function (e) {
        return handleKeyUp(e.originalEvent);
    });
});
  </script>
<?php minim()->end_block('page_js_head') ?>

<?php minim()->def_block('page_content') ?>
    <div id="board">
      <div id="hero"></div>
    </div>
<?php minim()->end_block('page_content') ?>

<?php include_once minim()->lib('flickr') ?>
<?php //include_once minim()->lib('twitter') ?>

<?php minim()->def_block('page_related') ?>
    <div class="column left-col">
     <div class="box">
<?php flickr_grid() ?>
     </div>
    </div>
    <div class="column right-col">
     <div class="box">
<?php //twitter_feed() ?>
     </div>
    </div>
<?php minim()->end_block('page_related') ?>
