<?php $this->extend('base') ?>

<?php $this->def_block('title') ?>MUD<?php $this->end_block('title') ?>

<?php $this->def_block('page_css') ?>
<link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/mud.css">
<?php $this->end_block('page_css') ?>

<?php $this->def_block('page_js_foot') ?>
<script type="text/javascript">
var user = <?php echo json_encode($user->to_array()) ?>;
var neighbours = <?php echo json_encode($neighbours) ?>;
</script>
<script type="text/javascript" src="<?php echo minim()->webroot ?>/js/mud.js"></script>
<?php $this->end_block('page_js_foot') ?>

<?php $this->def_block('page_content') ?>
User: <?php echo $user->user ?><br>
Area: <?php echo $area ?><br>
<textarea id="output"></textarea>
<?php $this->end_block('page_content') ?>
