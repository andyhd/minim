<?php $this->extend('base') ?>

<?php require_once minim()->lib('helpers') ?>

<?php $this->def_block('title') ?>Admin<?php $this->end_block('title') ?>

<?php $this->def_block('body_class') ?>admin<?php $this->end_block('body_class') ?>

<?php $this->def_block('page_content') ?>
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
        <tr<?php echo alternate(' class="alt"', '') ?>>
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
    elseif ($model->_fields[$field] instanceof BreveText and !$model->_fields[$field]->getAttribute('maxlength'))
    {
        $data .= htmlspecialchars(truncate($model->$field));
    }
    else
    {
        $data .= htmlspecialchars($model->$field);
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
<?php $this->end_block('page_content') ?>
