<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Login<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_content') ?>
   <form method="post">
    <h1>Login</h1>
<?php if ($errors): ?>
    <ul class="errors">
    <?php foreach ($errors as $error): ?>
     <li><?php echo $error ?></li>
    <?php endforeach ?>
    </ul>
<?php endif ?>
    <div>
     <?php echo $form->next->render() ?>
     <?php echo $form->name->label ?>
     <?php echo $form->name->render() ?>
    </div>
    <div>
     <?php echo $form->password->label ?>
     <?php echo $form->password->render() ?>
    </div>
    <div>
     <input type="submit" class="submit" value="Login">
    </div>
  </form>
<?php minim()->end_block('page_content') ?>

<?php include_once minim()->lib('flickr') ?>
<?php //include_once minim()->lib('twitter') ?>

<?php minim()->def_block('page_related') ?>
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
<?php minim()->end_block('page_related') ?>
