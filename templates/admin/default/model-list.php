<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Admin<?php minim()->end_block('title') ?>

<?php minim()->def_block('body_class') ?>admin<?php minim()->end_block('body_class') ?>

<?php minim()->def_block('page_content') ?>
    <h1><?php echo $model_name_plural ?></h1>
    <ul class="messages">
    <?php foreach (minim()->user_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <table class="model_list">
      <thead>
        <tr>
          <?php foreach ($model_fields as $field): ?>
          <th scope="col">
            <?php echo $field ?>
          </th>
          <?php endforeach ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($models as $model): ?>
        <tr>
          <?php foreach ($model_fields as $field): ?>
          <td><?php echo $model->$field ?></td>
          <?php endforeach ?>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
<?php minim()->end_block('page_content') ?>
