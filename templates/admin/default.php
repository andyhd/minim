<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Admin<?php minim()->end_block('title') ?>

<?php minim()->def_block('body_class') ?>admin<?php minim()->end_block('body_class') ?>

<?php minim()->def_block('page_content') ?>
    <h1>Admin Site</h1>
    <ul class="messages">
    <?php foreach (minim()->user_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <h3>Models</h3>
    <ul>
      <?php foreach ($models as $model => $file): ?>
      <li><a href="<?php echo minim()->url_for('admin/model-list', array('model' => $model)) ?>"><?php echo $model ?></a></li>
      <?php endforeach ?>
    </ul>
<?php minim()->end_block('page_content') ?>
