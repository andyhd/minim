<?php $this->extend('admin/base') ?>

<?php require_once minim()->lib('helpers') ?>

<?php $this->set('title') ?><?php echo $model_name_plural ?><?php $this->end() ?>

<?php $this->set('page_content') ?>
    <a href="<?php echo minim('routing')->url_for('admin/model-edit', array('model' => $model_name, 'id' => 'new')) ?>">Add new <?php echo $model_name ?></a>
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
        <?php foreach ($models as $model): ?>
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
    elseif ($model->_fields[$field]->_type == 'timestamp')
    {
        $data .= date('d M, Y @ H:i', $model->$field);
    }
    elseif ($model->_fields[$field]->_type == 'text' and !$model->_fields[$field]->attr('maxlength'))
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
<?php $this->end() ?>
