<ul class="pagination">
  <?php if ($prev): ?><li><a href="<?php echo $prev ?>" class="prev">Prev</a></li><?php endif ?>
  <?php for ($i = $from; $i <= $to; $i++): ?>
  <?php if ($i == $page): ?>
    <li><?php echo $i ?></li>
  <?php else: ?>
    <li><a href="<?php echo $url[$i] ?>"><?php echo $i ?></a></li>
  <?php endif ?>
  <?php endfor ?>
  <?php if ($next): ?><li><a href="<?php echo $next ?>" class="next">Next</a></li><?php endif ?>
</ul>
