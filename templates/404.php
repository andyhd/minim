<?php $this->extend('base') ?>

<?php $this->set('title') ?>Page Not Found<?php $this->end() ?>

<?php $this->set('page_content') ?>
  <h1>Page Not Found</h1>
  <p class="url"><?php echo $url ?></p>
  <p>Sorry, that address doesn't match any page.</p>
<?php $this->end() ?>

