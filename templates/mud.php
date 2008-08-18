User: <?php echo $user->id ?><br>
Map: <?php echo $area->map ?><br>
<ul>
<?php foreach ($neighbours->items as $neighbour): ?>
    <li>Neighbour: <?php echo $neighbour->id ?></li>
<?php endforeach ?>
</ul>
