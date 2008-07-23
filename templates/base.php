<?php minim()->extend('root') ?>

<?php minim()->def_block('title') ?><?php minim()->block('title') ?> - PageZero<?php minim()->end_block('title') ?>

<?php minim()->def_block('meta') ?>
  <link rel="shortcut icon" type="image/gif" href="<?php echo minim()->webroot ?>/images/favicon.gif">
<?php minim()->block('page_meta') ?>
<?php minim()->end_block('meta') ?>

<?php minim()->def_block('css') ?>
  <link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/reset-fonts.css">
  <link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/coffee.css">
<?php minim()->block('page_css') ?>
<?php minim()->end_block('css') ?>

<?php minim()->def_block('js_head') ?>
  <script type="text/javascript" src="<?php echo minim()->webroot ?>/js/jquery-1.2.6.min.js"></script>
<?php minim()->block('page_js_head') ?>
<?php minim()->end_block('js_head') ?>

<?php minim()->def_block('js_foot') ?>
  <script type="text/javascript">
jQuery(function() {
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
   <div id="sub-content">
    <ul id="navigation">
<?php navigation() ?>
    </ul>
<?php minim()->block('page_related') ?>
   </div>
   <div id="footer">
    <p>&copy; 2008 Andy Driver - powered by Minim</p>
   </div>
  </div>
<?php minim()->end_block('content') ?>
