<?php foreach ($navigation_tabs as $tab => $data): ?>
    <li<?php if ($tab == $current): ?> class="current"<?php endif ?>><a href="<?php echo $data['url'] ?>"><?php echo $data['label'] ?></a></li>
<?php endforeach ?>
