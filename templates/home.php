<?php minim('templates')->extend('base') ?>

<?php minim('templates')->def_block('title') ?>Home<?php minim('templates')->end_block('title') ?>

<?php minim('templates')->def_block('page_content') ?>
    <p>Content goes here.</p>
<?php minim('templates')->end_block('page_content') ?>

<?php include_once minim()->lib('flickr') ?>
<?php //include_once minim()->lib('twitter') ?>

<?php minim('templates')->def_block('page_related') ?>
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
<?php minim('templates')->end_block('page_related') ?>
