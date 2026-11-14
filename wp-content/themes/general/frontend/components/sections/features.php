<?php
$features = [
	['icon' => 'cart', 'title' => 'Free delivery', 'text' => 'Consectetur adipi elit lorem ipsum dolor sit amet.'],
	['icon' => 'cup', 'title' => 'Quality guarantee', 'text' => 'Dolor sit amet orem ipsu mcons ectetur adipi elit.'],
	['icon' => 'tag-price', 'title' => 'Daily offers', 'text' => 'Amet consectetur adipi elit loreme ipsum dolor sit.'],
	['icon' => 'chat-check', 'title' => '100% secure payment', 'text' => 'Rem Lopsum dolor sit amet, consectetur adipi elit.'],
];
?>
<section class="features">
	<div class="container features__inner" data-anim="cards">
		<?php foreach ($features as $feature) : ?>
			<div class="features__item" data-anim-item>
				<svg class="features__icon icon" data-anim-pop><use href="../assets/icons/sprite.svg#<?= $feature['icon'] ?>"></use></svg>
				<h3 class="features__title h3-title"><?= $feature['title'] ?></h3>
				<p class="features__text"><?= $feature['text'] ?></p>
			</div>
		<?php endforeach; ?>
	</div>
</section>
