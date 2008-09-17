<?php $this->extend('base') ?>

<?php $this->def_block('title') ?>Home<?php $this->end_block('title') ?>

<?php $this->def_block('page_content') ?>
    <p>Content goes here.</p>
<?php $this->end_block('page_content') ?>

<?php include_once minim()->lib('flickr') ?>
<?php //include_once minim()->lib('twitter') ?>

<?php $this->def_block('page_related') ?>
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
<?php $this->end_block('page_related') ?>
