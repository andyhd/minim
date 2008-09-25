<?php $this->extend('admin/base') ?>

<?php $this->set('title') ?>Models<?php $this->end() ?>

<?php $this->set('body_class') ?>models<?php $this->end() ?>

<?php $this->set('page_content') ?>
    <table>
      <thead>
        <tr>
          <th scope="col">Model</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($models as $model => $file): ?>
        <tr>
          <td><a href="<?php echo minim('routing')->url_for('admin/model-list', array('model' => $model)) ?>"><?php echo $model ?></a></td>
          <td><form method="post"><div><input type="hidden" name="create_table" value="<?php echo $model ?>"><input type="submit" value="(Re)build DB table"></div></form></td>
      <?php endforeach ?>
      </tbody>
    </table>
<?php $this->end() ?>
