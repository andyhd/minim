<?php minim()->extend('base') ?>

<?php minim()->def_block('title') ?>Blog Admin<?php minim()->end_block('title') ?>

<?php minim()->def_block('body_class') ?>admin<?php minim()->end_block('body_class') ?>

<?php minim()->def_block('page_content') ?>
    <h1>Manage Posts</h1>
    <p><a href="<?php echo minim()->url_for('admin/blog-post-new') ?>">New post</a></p>
    <form method="post">
      <table class="blog-posts">
        <thead>
          <tr>
            <th scope="col">Title</th>
            <th scope="col">Author</th>
            <th scope="col">Created</th>
            <th class="last-child"></th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($posts->items as $post): ?>
          <tr<?php echo alternate(' class="alt"', '') ?>>
            <td class="expand"><?php echo $post->title ?></td>
            <td><?php echo $post->author ?></td>
            <td><?php echo date('H:i:s d/m/Y', $post->posted) ?></td>
            <td class="last-child"><a href="<?php echo minim()->url_for('admin/blog-post-delete', array('id' => $post->id)) ?>" class="delete-link">Delete</a></td>
          </tr>
<?php endforeach ?>
        </tbody>
      </table>
    </form>
    <?php echo paginate($posts) ?>
<?php minim()->end_block('page_content') ?>
