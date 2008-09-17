<?php $this->extend('404') ?>

<?php $this->def_block('page_related') ?>
    <p class="box">
      You searched for <strong><?php echo $terms ?></strong>, but <?php echo $name ?>'s index appears to be out of date.
    </p>
<?php $this->end_block('page_related') ?>
