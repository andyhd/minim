<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>MUD<?php minim()->end_block('title') ?>

<?php minim()->def_block('page_css') ?>
<link rel="stylesheet" type="text/css" href="<?php echo minim()->webroot ?>/css/mud.css">
<?php minim()->end_block('page_css') ?>

<?php minim()->def_block('page_js_head') ?>
<script type="text/javascript">
var user = <?php echo json_encode($user->to_array()) ?>;
</script>
<?php minim()->end_block('page_js_head') ?>

<?php minim()->def_block('page_js_foot') ?>
<script type="text/javascript" src="<?php echo minim()->webroot ?>/js/mud.js"></script>
<?php minim()->end_block('page_js_foot') ?>

<?php minim()->def_block('page_content') ?>
User: <?php echo $user->user ?><br>
Area: <?php echo $area ?><br>
Neighbours: <ul>
<?php foreach ($neighbours->items as $avatar): ?>
    <li><?php echo $avatar->user ?></li>
<?php endforeach ?>
</ul>
Chat: <ul>
<?php foreach ($chat->items as $msg): ?>
    <li><?php echo $msg->user ?> says: <?php echo $msg->msg ?> at <?php echo $msg->at ?></li>
<?php endforeach ?>
</ul>
<textarea id="output"></textarea>
<?php minim()->end_block('page_content') ?>
