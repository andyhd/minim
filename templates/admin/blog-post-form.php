<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>New Blog Post<?php minim()->end_block('title') ?>

<?php minim()->def_block('body_class') ?>admin<?php minim()->end_block('body_class') ?>

<?php minim()->def_block('page_content') ?>
    <h1><?php if ($create): ?>New<?php else: ?>Edit<?php endif ?> Blog Post</h1>
    <?php if ($errors): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
        <li><?php echo $error ?></li>
        <?php endforeach ?>
    </ul>
    <?php endif ?>
    <form method="post">
      <?php if (@$post): ?><input type="hidden" name="id" value="<?php echo $post->id ?>"><?php endif ?>
      <div class="form-row">
        <label for="title-id">Title</label>
        <input id="title-id" type="text" name="title" value="<?php if (@$post) { echo $post->title; } ?>" size="40">
      </div>
      <div class="form-row">
        <label for="content-id">Content</label>
        <textarea id="content-id" name="content" rows="8" cols="40"><?php if (@$post) { echo $post->content; } ?></textarea>
      </div>
      <div class="form-row">
        <label for="tags-id">Tags</label>
        <input id="tags-id" type="text" name="tags" value="<?php if (@$post) { echo $post->tags; } ?>" size="40">
      </div>
      <div class="form-row submit">
        <input type="submit" value="Submit" class="submit">
      </div>
    </form>
<?php minim()->end_block('page_content') ?>
