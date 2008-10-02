<?php $this->extend('base') ?>

<?php $this->set('title') ?>Blog<?php $this->end() ?>

<?php $this->set('page_content') ?>
  <ol class="blog-posts">
<?php $i = 0; foreach ($posts as $post): ?>
   <li>
    <div class="post">
     <h2><a href="<?php echo url_for_blog_post($post) ?>"><?php echo $post->title ?></a></h2>
     <?php if ($post->teaser): ?>
      <?php echo $post->teaser ?>
     <?php else: ?>
      <?php echo truncate($post->content, 300) ?>
     <?php endif ?>
     <ol class="taglist">
     </ol>
    </div>
   </li>
<?php $i++; if ($i == 3) break; endforeach ?>
  </ol>
  <ol class="titles">
<?php $i = 0; foreach ($posts as $post): $i++; if ($i < 3) continue ?>
   <li><a href="<?php echo url_for_blog_post($post) ?>"><?php echo $post->title ?></a></li>
<?php endforeach ?>
  </ol>
  <p>
   <a href="<?php minim('routing')->url_for("blog-archive") ?>">Older Posts</a>
  </p>
<?php $this->end() ?>
