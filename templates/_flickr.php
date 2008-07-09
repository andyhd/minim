   <div id="flickr" class="img-grid">
    <h2>Recent photos</h2>
    <ol>
<?php foreach ($images as $photo): ?>
     <li><a href="<?php echo $photo['url'] ?>"><img src="<?php echo $photo['thumbnail'] ?>" width="90" height="90" alt="<?php echo $photo['title'] ?>"></a></li>
<?php endforeach ?>
    </ol>
    <a href="http://flickr.com/photos/pagezero">See all my photos</a>
   </div>
