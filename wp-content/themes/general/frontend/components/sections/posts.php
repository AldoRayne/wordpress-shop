<?php
$posts = [
	['title' => 'Technology hack that you don’t know', 'date' => 'feb 22, 2024', 'category' => 'technology', 'img' => 'post-1.jpg'],
	['title' => 'Best digital watches to buy in this year', 'date' => 'feb 22, 2024', 'category' => 'watches', 'img' => 'post-2.jpg'],
	['title' => 'How to use gimbal to take video', 'date' => 'feb 22, 2024', 'category' => 'Gadgets', 'img' => 'post-3.jpg'],
];
?>
<section class="posts">
	<div class="container">
		<h2 class="posts__title h2-title">Latest Posts</h2>
		<div class="posts__grid">
			<?php foreach ($posts as $post) : ?>
				<?php include('../components/elements/_post-card.php') ?>
			<?php endforeach; ?>
		</div>
		<div class="posts__more">
			<a class="btn" href="blog.php">Read blogs</a>
		</div>
	</div>
</section>
