<?php minim()->extend('root') ?>

<?php minim()->def_block('title') ?><?php minim()->block('title') ?> - PageZero<?php minim()->end_block('title') ?>

<?php minim()->def_block('meta') ?>
  <link rel="shortcut icon" type="image/gif" href="images/favicon.gif">
<?php minim()->block('page_meta') ?>
<?php minim()->end_block('meta') ?>

<?php minim()->def_block('css') ?>
  <link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
  <link rel="stylesheet" type="text/css" href="css/coffee.css">
<?php minim()->block('page_css') ?>
<?php minim()->end_block('css') ?>

<?php minim()->def_block('js_head') ?>
  <script type="text/javascript" src="js/jquery-1.2.6.min.js"></script>
<?php minim()->block('page_js_head') ?>
<?php minim()->end_block('js_head') ?>

<?php minim()->def_block('js_foot') ?>
  <script type="text/javascript">
$(function() {
    $('#mh_search :submit').hide();
    var input = $('#mh_search_input');
    var initial = $('label[for="mh_search_input"]').text()
    $('#mh_search_input').focus(function () {
        input.css('color', '#000');
        if (input.val() == initial) {
            input.val('');
        }
    }).blur(function() {
        if (input.val() == '') {
            input.val(initial).css('color', '#aaa');
        }
    }).css('color', '#aaa').val(initial);
});
  </script>
<?php minim()->block('page_js_foot') ?>
<?php minim()->end_block('js_foot') ?>

<?php include_once minim()->lib('random_engrish') ?>
<?php include_once minim()->lib('navigation_tabs') ?>
<?php include_once minim()->lib('flickr') ?>
<?php //include_once minim()->lib('twitter') ?>

<?php minim()->def_block('content') ?>
  <div id="wrapper">
   <div id="masthead">
    <h1>PageZero</h1>
    <p><?php random_engrish() ?></p>
    <form method="get" action="search" id="mh_search">
     <label for="mh_search_input">Search</label>
     <input name="search" id="mh_search_input" type="text" size="20" title="Search PageZero">
     <input type="submit" value="Go" class="submit">
    </form>
   </div>
   <div id="content">
<?php minim()->block('page_content') ?>
   </div>
   <ul id="navigation">
<?php navigation() ?>
   </ul>
   <div id="sub-content">
    <div class="column left-col">
     <div class="box">
      <p>Recent Flickr Photos</p>
<?php flickr_grid() ?>
     </div>
    </div>
    <div class="column right-col">
     <div class="box">
      <p>Twitter</p>
<?php //twitter_feed() ?>
     </div>
    </div>
   </div>
  </div>
<?php minim()->end_block('content') ?>
