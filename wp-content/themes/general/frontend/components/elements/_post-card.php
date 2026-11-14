<a class="post-card" href="<?= $post['href'] ?? 'blog-post.php' ?>">
	<span class="post-card__img">
		<img src="../assets/img/<?= $post['img'] ?>" alt="<?= $post['title'] ?>" width="490" height="356" loading="lazy">
	</span>
	<span class="post-card__body">
		<span class="post-card__meta"><?= $post['date'] ?>&nbsp; - &nbsp;<?= $post['category'] ?></span>
		<span class="post-card__title h3-title"><?= $post['title'] ?></span>
	</span>
</a>
