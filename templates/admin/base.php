<?php $this->extend('base') ?>

<?php $this->set('title') ?>Admin - <?php $this->get('title') ?><?php $this->end() ?>

<?php $this->set('body_class') ?>admin <?php $this->get('body_class') ?><?php $this->end() ?>

<?php $this->set('page_content') ?>
    <ul class="subnav">
      <li><a href="">Dashboard</a></li>
      <li><a href="">Routing</a></li>
      <li><a href="">Models</a></li>
    </ul>
    <h1><?php $this->get('title') ?></h1>
    <ul class="messages">
    <?php foreach (minim('user_messaging')->get_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <?php $this->get('page_content') ?>
<?php $this->end() ?>
