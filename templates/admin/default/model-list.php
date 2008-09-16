<?php minim('templates')->extend('base') ?>

<?php minim('templates')->def_block('title') ?>Admin<?php minim('templates')->end_block('title') ?>

<?php minim('templates')->def_block('body_class') ?>admin<?php minim('templates')->end_block('body_class') ?>

<?php minim('templates')->def_block('page_content') ?>
    <h1><?php echo $model_name_plural ?></h1>
    <ul class="messages">
    <?php foreach (minim('user_messaging')->get_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <table class="model_list">
      <thead>
        <tr>
          <?php
$row = '';
foreach ($model_fields as $field)
{
    if ($field == 'id')
    {
        continue;
    }
    else
    {
        $heading = $field;
    }
    $row .= "<th scope=\"col\">$heading</th>";
}
echo $row;
?>
          <th></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($models->items as $model): ?>
        <tr>
          <?php
$row = '';
foreach ($model_fields as $field)
{
    $data = '';
    if ($field == 'id')
    {
        continue;
    }
    elseif ($model->_fields[$field] instanceof BreveTimestamp)
    {
        $data .= date('d M, Y @ H:i', $model->$field);
    }
    else
    {
        $data .= $model->$field;
    }
    $row .= "<td>$data</td>";
}
echo $row;
?>
          <td><a href="<?php echo minim('routing')->url_for('admin/model-edit', array('model' => $model_name, 'id' => $model->id)) ?>" class="delete-link">Edit</a></td>
          <td><a href="<?php echo minim('routing')->url_for('admin/model-delete', array('model' => $model_name, 'id' => $model->id)) ?>" class="delete-link">Delete</a></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <?php echo minim('pagination')->paginate($models) ?>
<?php minim('templates')->end_block('page_content') ?>
