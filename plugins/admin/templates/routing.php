<?php $this->extend('base') ?>

<?php $this->set('title') ?>Routing<?php $this->end() ?>

<?php $this->set('body_class') ?>routing<?php $this->end() ?>

<?php $this->set('page_css') ?>
<?php $this->include_css('admin') ?>
<?php $this->end() ?>

<?php $this->set('page_js_foot') ?>
<?php $this->include_js('map-url-form') ?>
<?php $this->end() ?>

<?php $this->set('page_content') ?>
<form method="post">
<table>
  <thead>
    <tr>
      <th></th>
      <th scope="col">Pattern</th>
      <th scope="col">View</th>
      <th scope="col">Action</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
<?php
$i = 0;
foreach ($url_map as $map):
?>
    <tr>
      <td><input type="checkbox" name="sel_<?php echo $i ?>"></td>
      <td><input type="text" name="pattern_<?php echo $i ?>" value="<?php echo htmlspecialchars($map['url_pattern']) ?>"></td>
      <td><input type="text" name="view_<?php echo $i ?>" value="<?php echo htmlspecialchars($map['view']) ?>"></td>
      <td><input type="text" name="action_<?php echo $i ?>" value="<?php echo htmlspecialchars($map['action']) ?>"></td>
      <td><a href="" class="deletelink">Delete</a></td>
    </tr>
<?php
$i++;
endforeach;
?>
    <tr>
      <td></td>
      <td><input type="text" name="pattern_new"></td>
      <td><input type="text" name="view_new"></td>
      <td><input type="text" name="action_new"></td>
      <td><a href="" id="add-new-map">Add</a></td>
    </tr>
  </tbody>
</table>
<div>
  <input type="submit" value="Generate mod_rewrite rules">
</div>
</form>
<?php $this->end() ?>
