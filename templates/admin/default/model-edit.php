<?php $this->extend('base') ?>

<?php $this->set('title') ?>Admin<?php $this->end() ?>

<?php $this->set('body_class') ?>admin<?php $this->end() ?>

<?php $this->set('page_content') ?>
    <h1>Edit <?php echo $model_name ?></h1>
    <ul class="messages">
    <?php foreach (minim('user_messaging')->get_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <form id="model-edit-form" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
<?php if ($errors): ?>
    <ul class="errors">
    <?php foreach ($errors as $error): ?>
     <li><?php echo $error ?></li>
    <?php endforeach ?>
    </ul>
<?php endif ?>
<?php foreach ($form->_fields as $field => $val): ?>
    <div class="form-row">
      <?php echo $form->$field->label ?>
      <?php echo $form->$field->render() ?>
    </div>
<?php endforeach ?>
    <div class="form-row">
      <input type="submit" class="submit" value="Save">
    </div>
    </form>
<?php $this->end() ?>
