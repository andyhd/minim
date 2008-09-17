<?php $this->extend('root') ?>

<?php $this->def_block('title') ?><?php $this->block('title') ?> - PageZero<?php $this->end_block('title') ?>

<?php $this->def_block('meta') ?>
  <link rel="shortcut icon" type="image/gif" href="<?php echo minim()->webroot ?>/images/favicon.gif">
<?php $this->block('page_meta') ?>
<?php $this->end_block('meta') ?>

<?php $this->def_block('css') ?>
  <link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/reset-fonts.css">
  <link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/coffee.css">
<?php $this->block('page_css') ?>
<?php $this->end_block('css') ?>

<?php $this->def_block('js_head') ?>
  <script type="text/javascript" src="<?php echo minim()->webroot ?>/js/jquery-1.2.6.min.js"></script>
<?php $this->block('page_js_head') ?>
<?php $this->end_block('js_head') ?>

<?php $this->def_block('js_foot') ?>
  <script type="text/javascript" src="<?php echo minim()->webroot ?>/js/label_inside.js"></script>
<?php $this->block('page_js_foot') ?>
<?php $this->end_block('js_foot') ?>

<?php include_once minim()->lib('random_engrish') ?>
<?php include_once minim()->lib('navigation_tabs') ?>

<?php $this->def_block('content') ?>
  <div id="wrapper">
   <div id="masthead">
<?php if (@$logo_is_h1): ?>
    <h1 id="logo">PageZero</h1>
<?php else: ?>
    <div id="logo">PageZero</div>
<?php endif ?>
    <div class="logged_in">
<?php if (minim()->user()): ?>
     Logged in as <strong><?php $user = minim()->user(); echo $user['name'] ?></strong> - <a href="<?php echo minim('routing')->url_for('logout') ?>">Log out</a>
<?php else: ?>
     <a href="<?php echo minim('routing')->url_for('login') ?>">Log in</a> - <a href="<?php echo minim('routing')->url_for('sign-up') ?>">Sign up</a>
<?php endif ?>
    </div>
    <p><?php random_engrish() ?></p>
    <form method="get" action="search" id="mh_search">
     <label for="mh_search_input">Search</label>
     <input name="search" id="mh_search_input" type="text" size="20" title="Search PageZero">
     <input type="submit" value="Go" class="submit">
    </form>
   </div>
   <div id="content">
<?php $this->block('page_content') ?>
   </div>
   <div id="sub-content">
    <ul id="navigation">
<?php navigation() ?>
    </ul>
<?php $this->block('page_related') ?>
   </div>
   <div id="footer">
    <p>&copy; 2008 Andy Driver - powered by Minim</p>
   </div>
  </div>
<?php $this->end_block('content') ?>
