<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Blog<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_content') ?>
  <ol class="blog-posts">
<?php foreach ($posts as $post): ?>
   <li><a href="<?php echo url_for_blog_post($post) ?>"><?php echo $post['title'] ?></a></li>
<?php endforeach ?>
  </ol>
<?php minim()->end_block('page_content') ?>
