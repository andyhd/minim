<?php $this->extend('base') ?>

<?php $this->set('title') ?>MUD<?php $this->end() ?>

<?php $this->set('page_css') ?>
<?php $this->include_css('mud') ?>
<?php $this->end() ?>

<?php $this->set('page_js_foot') ?>
<script type="text/javascript">
var user = <?php echo json_encode($user->to_array()) ?>;
var neighbours = <?php echo json_encode($neighbours) ?>;
</script>
<?php $this->include_js('mud') ?>
<?php $this->end() ?>

<?php $this->set('page_content') ?>
User: <?php echo $user->user ?><br>
Area: <?php echo $area ?><br>
<textarea id="output"></textarea>
<?php $this->end() ?>
