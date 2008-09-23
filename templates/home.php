<?php $this->extend('base') ?>

<?php $this->set('title') ?>Home<?php $this->end() ?>

<?php $this->set('page_content') ?>
    <p>Content goes here.</p>
<?php $this->end() ?>

<?php include_once minim()->lib('flickr') ?>
<?php //include_once minim()->lib('twitter') ?>

<?php $this->set('page_related') ?>
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
<?php $this->end() ?>
