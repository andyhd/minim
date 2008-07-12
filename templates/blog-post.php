<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?><?php echo $post->title ?><?php minim()->end_block('title') ?>

<?php minim()->def_block('page_content') ?>
  <div class="post">
   <h2><?php echo $post->title ?></h2>
   <?php echo $post->content ?>
   <ol class="taglist">
   </ol>
  </div>
<?php minim()->end_block('page_content') ?>

<?php minim()->def_block('page_related') ?>
  <div id="comments">
<?php foreach ($comments as $i => $comment): ?>
   <div class="box comment<?php echo $i+1 == count($comments) ? ' last-child' : '' ?>">
    <p class="attribution">
     <span class="author"><?php echo $comment->author ?></span> said:
    </p>
    <?php echo $comment->content ?>
    <p class="posted"><?php echo date('H:i - d M Y', $comment->posted) ?></p>
   </div>
<?php endforeach ?>
  </div>
  <form id="comment-form" method="post" class="box">
    <h3>Add A Comment</h3>
    <div>
     <label for="name_id">Name</label>
     <input type="text" name="name" id="name_id">
    </div>
    <div>
     <label for="email_id">Email</label>
     <input type="text" name="email" id="email_id">
     <p class="help-text">Will not be published.</p>
    </div>
    <div>
     <label for="comment_id">Comment</label>
     <textarea name="comment" rows="6" id="comment_id"></textarea>
    </div>
    <div>
     <input type="submit" class="submit" value="Post comment">
    </div>
  </form>
<?php minim()->end_block('page_related') ?>
