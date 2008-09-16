<?php minim('templates')->extend('base') ?>

<?php minim('templates')->def_block('title') ?>Blog<?php minim('templates')->end_block('title') ?>

<?php minim('templates')->def_block('page_content') ?>
  <ol class="blog-posts">
<?php for ($i = 0; $i < 3 and $i < count($posts->items); $i++): $post = $posts->items[$i] ?>
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
<?php endfor ?>
  </ol>
  <ol class="titles">
<?php for ($i = 3; $i < count($posts->items); $i++): $post = $posts->items[$i] ?>
   <li><a href="<?php echo url_for_blog_post($post) ?>"><?php echo $post->title ?></a></li>
<?php endfor ?>
  </ol>
  <p>
   <a href="<?php minim('routing')->url_for("blog-archive") ?>">Older Posts</a>
  </p>
<?php minim('templates')->end_block('page_content') ?>
