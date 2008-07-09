<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Blog<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_content') ?>
  <div class="post">
   <h2><?php echo $post['title'] ?></h2>
   <?php echo $post['content'] ?>
   <ol class="taglist">
   </ol>
  </div>
  <form id="comment-form" method="post">
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
  <div id="comments">
<?php foreach ($comments as $comment): ?>
   <div class="comment">
    <?php echo $comment['text'] ?>
    <p class="attribution">
     <span class="author"><?php echo $comment['author'] ?></span>
     <span class="posted"><?php echo date('H:i d M y', $comment['posted']) ?></span>
    </p>
   </div>
<?php endforeach ?>
  </div>
<?php minim()->end_block('page_content') ?>
