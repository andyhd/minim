<?php $this->extend('admin_base') ?>

<?php $this->set('title') ?>New Blog Post<?php $this->end() ?>

<?php $this->set('page_content') ?>
    <?php if ($errors): ?>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
        <li><?php echo $error ?></li>
        <?php endforeach ?>
    </ul>
    <?php endif ?>
    <form method="post">
<?php foreach ($form->_fields as $field => $val): ?>
      <div class="form-row">
        <?php echo $form->$field->label ?>
        <?php echo $form->$field->render() ?>
      </div>
<?php endforeach ?>
      <div class="form-row submit">
        <input type="submit" class="submit" value="Save">
      </div>
    </form>
<?php $this->end() ?>
