<?php $this->extend('404') ?>

<?php $this->set('page_related') ?>
    <p class="box">
      You searched for <strong><?php echo $terms ?></strong>, but <?php echo $name ?>'s index appears to be out of date.
    </p>
<?php $this->end() ?>
