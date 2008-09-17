<?php $this->extend('base') ?>

<?php $this->def_block('title') ?><?php echo $post->title ?><?php $this->end_block('title') ?>

<?php $this->def_block('page_content') ?>
  <div class="post">
   <h2><?php echo $post->title ?></h2>
   <?php echo $post->content ?>
   <ol class="taglist">
   </ol>
  </div>
<?php $this->end_block('page_content') ?>

<?php $this->def_block('page_related') ?>
  <div id="comments">
<?php foreach ($comments->items as $i => $comment): ?>
   <div class="box comment<?php echo $i+1 == count($comments->items) ? ' last-child' : '' ?>">
    <p class="attribution">
     <span class="author">
      <?php if ($comment->email): ?>
      <a href="mailto:<?php echo $comment->name ?> &lt;<?php echo $comment->email ?>&gt;"><?php echo $comment->name ?></a>
      <?php else: ?>
      <?php echo $comment->name ?>
      <?php endif ?>
      </span>
      said:
    </p>
    <?php echo $comment->content ?>
    <p class="posted"><?php echo date('H:i - d M Y', $comment->posted) ?></p>
   </div>
<?php endforeach ?>
  </div>
  <form id="comment-form" method="post" class="box" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
    <h3>Add A Comment</h3>
<?php if ($errors): ?>
    <ul class="errors">
    <?php foreach ($errors as $error): ?>
     <li><?php echo $error ?></li>
    <?php endforeach ?>
    </ul>
<?php endif ?>
    <div>
     <?php echo $form->post_id->render() ?>
     <?php echo $form->name->label ?>
     <?php echo $form->name->render() ?>
    </div>
    <div>
     <?php echo $form->email->label ?>
     <?php echo $form->email->render() ?>
     <p class="help-text">Will not be published.</p>
    </div>
    <div>
     <?php echo $form->content->label ?>
     <?php echo $form->content->render() ?>
    </div>
    <div>
     <input type="submit" class="submit" value="Post comment">
    </div>
  </form>
<?php $this->end_block('page_related') ?>
