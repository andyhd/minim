<?php $this->extend('base') ?>

<?php $this->def_block('title') ?>Blog Admin<?php $this->end_block('title') ?>

<?php $this->def_block('body_class') ?>admin<?php $this->end_block('body_class') ?>

<?php $this->def_block('page_content') ?>
    <h1>Manage Posts</h1>
    <ul class="messages">
    <?php foreach (minim()->user_messages() as $msg): ?>
      <li><?php echo $msg ?></li>
    <?php endforeach ?>
    </ul>
    <p><a href="<?php echo minim()->url_for('admin/blog-post:edit') ?>">New post</a></p>
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
<?php foreach ($posts as $post): ?>
          <tr<?php echo alternate(' class="alt"', '') ?>>
            <td class="expand"><a href="<?php echo minim()->url_for('admin/blog-post:edit', array('id' => $post->id)) ?>"><?php echo $post->title ?></a></td>
            <td><?php echo $post->author ?></td>
            <td><?php echo date('H:i:s d/m/Y', $post->posted) ?></td>
            <td class="last-child"><a href="<?php echo minim()->url_for('admin/blog-post:delete', array('id' => $post->id)) ?>" class="delete-link">Delete</a></td>
          </tr>
<?php endforeach ?>
        </tbody>
      </table>
    </form>
    <?php echo paginate($posts) ?>
<?php $this->end_block('page_content') ?>
