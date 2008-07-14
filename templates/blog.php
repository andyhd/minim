<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Blog<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_content') ?>
  <ol class="blog-posts">
<?php foreach ($posts as $post): ?>
   <li>
    <div class="post">
     <h2><a href="<?php echo url_for_blog_post($post) ?>"><?php echo $post->title ?></a></h2>
     <?php if ($post->teaser): ?>
      <?php echo $post->teaser ?>
     <?php else: ?>
      <?php echo minim()->truncate($post->content, 300) ?>
     <?php endif ?>
     <ol class="taglist">
     </ol>
    </div>
   </li>
<?php endforeach ?>
  </ol>
  <p>
   <a href="<?php minim()->url_for("blog-archive") ?>">Older Posts</a>
  </p>
<?php minim()->end_block('page_content') ?>
