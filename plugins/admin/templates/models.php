<?php $this->extend('admin_base') ?>

<?php $this->set('title') ?>Models<?php $this->end() ?>

<?php $this->set('body_class') ?>models<?php $this->end() ?>

<?php $this->set('page_content') ?>
    <table>
      <thead>
        <tr>
          <th scope="col">Model</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($models as $model): ?>
        <tr>
          <td><a href="<?php echo minim('routing')->url_for('admin/model-list', array('model' => $model)) ?>"><?php echo $model ?></a></td>
      <?php endforeach ?>
      </tbody>
    </table>
<?php $this->end() ?>
