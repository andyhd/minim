<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Page Not Found<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_content') ?>
  <h1>Page Not Found</h1>
  <p class="url"><?php echo $url ?></p>
  <p>Sorry, that address doesn't match any page.</p>
<?php minim()->end_block('page_content') ?>

