<?php $this->extend('admin/base') ?>

<?php $this->set('title') ?>Models<?php $this->end() ?>

<?php $this->set('body_class') ?>models<?php $this->end() ?>

<?php $this->set('page_content') ?>
    <ul>
      <?php foreach ($models as $model => $file): ?>
      <li><a href="<?php echo minim('routing')->url_for('admin/model-list', array('model' => $model)) ?>"><?php echo $model ?></a></li>
      <?php endforeach ?>
    </ul>
<?php $this->end() ?>
