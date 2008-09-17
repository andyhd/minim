<?php $this->extend('base') ?>

<?php $this->def_block('title') ?>Admin<?php $this->end_block('title') ?>

<?php $this->def_block('body_class') ?>admin<?php $this->end_block('body_class') ?>

<?php $this->def_block('page_content') ?>
    <h1>Admin Site</h1>
    <ul class="messages">
    <?php foreach (minim('user_messaging')->get_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <h3>Models</h3>
    <ul>
      <?php foreach ($models as $model => $file): ?>
      <li><a href="<?php echo minim('routing')->url_for('admin/model-list', array('model' => $model)) ?>"><?php echo $model ?></a></li>
      <?php endforeach ?>
    </ul>
<?php $this->end_block('page_content') ?>
