<?php foreach ($navigation_tabs as $tab => $label): ?>
    <li<?php if ($tab == $current): ?> class="current"<?php endif ?>><a href="<?php echo minim('routing')->url_for($tab) ?>"><?php echo $label ?></a></li>
<?php endforeach ?>
