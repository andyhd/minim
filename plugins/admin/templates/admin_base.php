<?php $this->extend('admin_root') ?>

<?php $this->set('meta') ?>
  <link rel="shortcut icon" type="image/gif" href="<?php echo minim()->webroot ?>/images/favicon.gif">
<?php $this->get('page_meta') ?>
<?php $this->end() ?>

<?php $this->set('css') ?>
<?php $this->include_css('reset-fonts') ?>
<?php $this->include_css('position') ?>
<?php $this->include_css('skin') ?>
<?php $this->get('page_css') ?>
<?php $this->end() ?>

<?php $this->set('js_head') ?>
<?php $this->include_js('jquery-1.2.6.min') ?>
<?php $this->get('page_js_head') ?>
<?php $this->end() ?>

<?php $this->set('js_foot') ?>
<?php $this->include_js('label_inside') ?>
<?php $this->get('page_js_foot') ?>
<?php $this->end() ?>

<?php $this->set('content') ?>
  <div id="wrapper">
   <div id="masthead">
    <h1>Minim Admin</div>
    <div class="logged_in">
<?php if (minim()->user()): ?>
     Logged in as <strong><?php $user = minim()->user(); echo $user['name'] ?></strong> - <a href="<?php echo minim('routing')->url_for('logout') ?>">Log out</a>
<?php else: ?>
     <a href="<?php echo minim('routing')->url_for('admin-login') ?>">Log in</a>
<?php endif ?>
    </div>
    <p>Some stuff</p>
    <form method="get" action="search" id="mh_search">
     <label for="mh_search_input">Search</label>
     <input name="search" id="mh_search_input" type="text" size="20" title="Search PageZero">
     <input type="submit" value="Go" class="submit">
    </form>
   </div>
   <div id="content">
    <ul class="subnav">
      <li><a href="<?php echo minim('routing')->url_for('admin-default') ?>">Dashboard</a></li>
      <li><a href="<?php echo minim('routing')->url_for('admin-models') ?>">Models</a></li>
    </ul>
    <h1><?php $this->get('title') ?></h1>
    <ul class="messages">
    <?php foreach (minim('user_messaging')->get_messages() as $msg): ?>
      <li><?php echo $msg[0] ?></li>
    <?php endforeach ?>
    </ul>
    <?php $this->get('page_content') ?>
   </div>
   <div id="footer">
    <p>&copy; 2008 Andy Driver - powered by Minim</p>
   </div>
  </div>
<?php $this->end() ?>

<?php $this->set('body_class') ?>admin <?php $this->get('body_class') ?><?php $this->end() ?>

<?php $this->set('title') ?>Admin - <?php $this->get('title') ?><?php $this->end() ?>


