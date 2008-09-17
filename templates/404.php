<?php $this->extend('base') ?>

<?php $this->def_block('title') ?>Page Not Found<?php $this->end_block('title') ?>

<?php $this->def_block('page_content') ?>
  <h1>Page Not Found</h1>
  <p class="url"><?php echo $url ?></p>
  <p>Sorry, that address doesn't match any page.</p>
<?php $this->end_block('page_content') ?>

